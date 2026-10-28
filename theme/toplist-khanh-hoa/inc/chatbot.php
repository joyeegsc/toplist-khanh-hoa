<?php
/**
 * Travel assistant chatbot: POST /wp-json/tkh/v1/chat.
 *
 * 1. Finds the site's articles most relevant to the question (current language).
 * 2. Asks free AI providers in order (Gemini → Groq → OpenRouter → OrcaRouter, whichever have
 *    API keys in the environment), moving on when one is out of quota or failing.
 * 3. When no provider answers, returns the matching articles instead (search mode).
 *
 * Protections: per-IP limits, message length cap, 24h answer cache, no message logging.
 * Keys come from environment variables (Render → Environment) or wp-config constants.
 */

defined( 'ABSPATH' ) || exit;

const TKH_CHAT_MAX_CHARS   = 500;
const TKH_CHAT_PER_MINUTE  = 6;
const TKH_CHAT_PER_DAY     = 40;
const TKH_CHAT_CACHE_HOURS = 24;

/**
 * Configured providers in fallback order. Each: id, label, base URL, key, model.
 * Filterable via `tkh_chat_providers` (also used by tests to add a mock provider).
 */
function tkh_chat_providers() {
	$env = function ( $name, $default = '' ) {
		if ( defined( $name ) ) {
			return (string) constant( $name );
		}
		$value = getenv( $name );
		return false === $value || '' === $value ? $default : $value;
	};
	$providers = array(
		array( 'id' => 'gemini', 'label' => 'Google Gemini', 'base' => 'https://generativelanguage.googleapis.com/v1beta/openai', 'key' => $env( 'GEMINI_API_KEY' ), 'model' => $env( 'GEMINI_MODEL', 'gemini-flash-lite-latest' ) ),
		array( 'id' => 'groq', 'label' => 'Groq', 'base' => 'https://api.groq.com/openai/v1', 'key' => $env( 'GROQ_API_KEY' ), 'model' => $env( 'GROQ_MODEL', 'llama-3.3-70b-versatile' ) ),
		array( 'id' => 'openrouter', 'label' => 'OpenRouter', 'base' => 'https://openrouter.ai/api/v1', 'key' => $env( 'OPENROUTER_API_KEY' ), 'model' => $env( 'OPENROUTER_MODEL', 'openrouter/free' ) ),
		array( 'id' => 'orcarouter', 'label' => 'OrcaRouter', 'base' => 'https://api.orcarouter.ai/v1', 'key' => $env( 'ORCAROUTER_API_KEY' ), 'model' => $env( 'ORCAROUTER_MODEL', 'orcarouter/free' ) ),
	);
	$providers = apply_filters( 'tkh_chat_providers', $providers );
	return array_values( array_filter( $providers, fn( $p ) => '' !== $p['key'] ) );
}

add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'tkh/v1',
			'/chat',
			array(
				'methods'             => 'POST',
				'callback'            => 'tkh_chat_handle',
				'permission_callback' => '__return_true',
				'args'                => array(
					'message' => array( 'type' => 'string', 'required' => true ),
					'lang'    => array( 'type' => 'string', 'default' => 'vi' ),
					'history' => array( 'type' => 'array', 'default' => array() ),
				),
			)
		);
	}
);

/**
 * Client IP: Render sits behind Cloudflare, which sets CF-Connecting-IP (not client-spoofable there).
 */
function tkh_chat_client_ip() {
	foreach ( array( 'HTTP_CF_CONNECTING_IP', 'REMOTE_ADDR' ) as $header ) {
		if ( ! empty( $_SERVER[ $header ] ) ) {
			$ip = trim( explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) ) )[0] );
			if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				return $ip;
			}
		}
	}
	return '0.0.0.0';
}

/**
 * Counts one request against the per-minute and per-day limits. Returns true when allowed.
 * Only a hash of the IP is stored, and it expires with the window.
 */
function tkh_chat_rate_ok() {
	$hash = substr( hash( 'sha256', wp_salt( 'nonce' ) . tkh_chat_client_ip() ), 0, 20 );
	foreach ( array( 'm' => array( MINUTE_IN_SECONDS, TKH_CHAT_PER_MINUTE ), 'd' => array( DAY_IN_SECONDS, TKH_CHAT_PER_DAY ) ) as $window => $rule ) {
		$key   = 'tkh_chat_' . $window . '_' . $hash;
		$count = (int) get_transient( $key );
		if ( $count >= $rule[1] ) {
			return false;
		}
		set_transient( $key, $count + 1, $rule[0] );
	}
	return true;
}

function tkh_chat_normalize( $text ) {
	$text = mb_strtolower( remove_accents( wp_strip_all_tags( (string) $text ) ) );
	return trim( preg_replace( '/[^a-z0-9]+/', ' ', $text ) );
}

/**
 * Lowercase words keeping Vietnamese diacritics: "bùn" (mud) and "bún" (noodles) stay distinct.
 */
function tkh_chat_words( $text ) {
	$text = mb_strtolower( wp_strip_all_tags( (string) $text ) );
	return trim( preg_replace( '/[^\p{L}\p{N}]+/u', ' ', $text ) );
}

/**
 * Searchable index of published places in one language (rebuilt when posts change).
 */
function tkh_chat_index( $lang ) {
	$key   = 'tkh_chat_index_v3_' . $lang;
	$index = get_transient( $key );
	if ( is_array( $index ) ) {
		return $index;
	}
	$args = array( 'post_type' => 'post', 'posts_per_page' => 200, 'no_found_rows' => true );
	if ( tkh_has_pll() ) {
		$args['lang'] = $lang;
	}
	$index = array();
	foreach ( get_posts( $args ) as $post ) {
		$cat    = tkh_post_category( $post->ID );
		$region = tkh_post_region( $post->ID );
		$tags   = wp_get_post_terms( $post->ID, 'post_tag', array( 'fields' => 'names' ) );
		$body   = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( strip_shortcodes( $post->post_content ) ) ) );
		$index[] = array(
			'id'       => $post->ID,
			'title'    => get_the_title( $post ),
			'place'    => tkh_place_name( $post->ID ),
			'url'      => get_permalink( $post ),
			'image'    => tkh_image_url( 'thumbnail', $post->ID ),
			'category' => $cat ? $cat->name : '',
			'region'   => $region ? $region->name : '',
			'address'  => tkh_meta( 'address', $post->ID ),
			'price'    => tkh_meta( 'price', $post->ID ),
			'hours'    => tkh_meta( 'hours', $post->ID ),
			'rating'   => tkh_meta( 'rating', $post->ID ),
			'excerpt'  => $post->post_excerpt,
			'body'     => mb_substr( $body, 0, 1400 ),
			// Weighted search text (title/place > area, category, tags > body), kept with diacritics;
			// the accent-folded copy serves questions typed without diacritics.
			'heading'  => tkh_chat_words( get_the_title( $post ) . ' ' . tkh_place_name( $post->ID ) ),
			'facets'   => tkh_chat_words( implode( ' ', array_merge( array( $cat ? $cat->name : '', $region ? $region->name : '', tkh_meta( 'address', $post->ID ) ), is_array( $tags ) ? $tags : array() ) ) ),
			'text'     => tkh_chat_words( $post->post_excerpt . ' ' . $body ),
		);
	}
	set_transient( $key, $index, DAY_IN_SECONDS );
	return $index;
}

add_action(
	'save_post_post',
	function () {
		delete_transient( 'tkh_chat_index_v3_vi' );
		delete_transient( 'tkh_chat_index_v3_en' );
	}
);

/**
 * Top places for a question: keyword overlap weighted by field.
 */
function tkh_chat_retrieve( $question, $lang, $limit = 4 ) {
	// Stop words compared in accent-folded form.
	$stop = array( 'o', 'va', 'la', 'cua', 'cho', 'nao', 'gi', 'dau', 'co', 'khong', 'mot', 'nhung', 'cac', 'toi', 'minh', 'ban', 'thi', 'the', 'and', 'or', 'a', 'an', 'is', 'are', 'in', 'of', 'to', 'for', 'what', 'where', 'how', 'which', 'with', 'on', 'at', 'i', 'me', 'my', 'do', 'can', 'nen', 'di', 'tai', 'giup', 'hay', 'nhe', 'ai', 'please' );
	// Typed with diacritics → match exactly (bùn ≠ bún); typed without → match accent-folded text.
	$folded = ! preg_match( '/[^\x00-\x7F]/', $question );
	$prep   = $folded ? 'tkh_chat_normalize' : 'tkh_chat_words';
	$terms  = array();
	foreach ( array_unique( explode( ' ', $prep( $question ) ) ) as $term ) {
		if ( mb_strlen( $term ) >= 2 && ! in_array( tkh_chat_normalize( $term ), $stop, true ) ) {
			$terms[] = $term;
		}
	}
	if ( ! $terms ) {
		return array();
	}
	$scored = array();
	foreach ( tkh_chat_index( $lang ) as $doc ) {
		$fields = array( 'heading' => $doc['heading'], 'facets' => $doc['facets'], 'text' => $doc['text'] );
		if ( $folded ) {
			$fields = array_map( 'tkh_chat_normalize', $fields );
		}
		$score = 0;
		foreach ( $terms as $term ) {
			$pattern = '/(?<![\p{L}\p{N}])' . preg_quote( $term, '/' ) . '(?![\p{L}\p{N}])/u';
			$score  += 6 * preg_match_all( $pattern, $fields['heading'] );
			$score  += 3 * preg_match_all( $pattern, $fields['facets'] );
			$score  += min( 4, preg_match_all( $pattern, $fields['text'] ) );
		}
		// Bonus when consecutive question words appear together (e.g. "hải sản", "nha trang").
		$all = ' ' . implode( ' ', $fields ) . ' ';
		for ( $i = 0; $i < count( $terms ) - 1; $i++ ) {
			if ( false !== mb_strpos( $all, ' ' . $terms[ $i ] . ' ' . $terms[ $i + 1 ] . ' ' ) ) {
				$score += 4;
			}
		}
		if ( $score > 0 ) {
			$scored[] = array( $score, $doc );
		}
	}
	usort( $scored, fn( $a, $b ) => $b[0] <=> $a[0] );
	// Drop weak matches (e.g. places that only share the city name) relative to the best one.
	$floor  = $scored ? 0.4 * $scored[0][0] : 0;
	$scored = array_filter( $scored, fn( $s ) => $s[0] >= $floor );
	return array_map( fn( $s ) => $s[1], array_slice( $scored, 0, $limit ) );
}

function tkh_chat_system_prompt( $lang, array $docs ) {
	$language = 'en' === $lang ? 'English' : 'Vietnamese (tiếng Việt)';
	$context  = array();
	foreach ( $docs as $i => $doc ) {
		$context[] = sprintf(
			"[%d] %s\nURL: %s\nCategory: %s | Area: %s | Address: %s | Price: %s | Hours: %s | Rating: %s/5\nSummary: %s\nArticle excerpt: %s",
			$i + 1,
			$doc['title'],
			$doc['url'],
			$doc['category'],
			$doc['region'],
			$doc['address'],
			$doc['price'],
			$doc['hours'],
			$doc['rating'],
			$doc['excerpt'],
			$doc['body']
		);
	}
	return implode(
		"\n",
		array(
			'You are the travel assistant of "Toplist Khánh Hòa", a guide to Khánh Hòa province, Vietnam (Nha Trang, Cam Ranh, Cam Lâm, Ninh Hòa, Vạn Ninh, Phan Rang & Vĩnh Hy).',
			"Always answer in {$language}, friendly and concise (at most about 170 words). Use short paragraphs or '- ' bullet lists.",
			'Base recommendations on the site articles below. EVERY place that has an article MUST be written as a bold markdown link with exactly the URL given, e.g. **[Hải Sản Mộc Quán](https://example.com/hai-san-moc-quan/)**. Never invent URLs or add other links.',
			'You may add widely known general facts about Khánh Hòa (weather, seasons, transport), but do not invent specific prices, phone numbers or opening hours; say prices are for reference and may change.',
			'If the question is not about travel, food, stays or life in Khánh Hòa, politely say you can only help with Khánh Hòa travel and suggest a related question.',
			'Do not ask for or store personal information.',
			'',
			$context ? "Site articles:\n" . implode( "\n\n", $context ) : 'No matching site article was found for this question; answer from general knowledge carefully and suggest browsing the site categories.',
		)
	);
}

/**
 * Calls one OpenAI-compatible provider. Returns the answer text, or WP_Error (quota/network/format).
 */
function tkh_chat_call_provider( array $provider, array $messages ) {
	$response = wp_remote_post(
		rtrim( $provider['base'], '/' ) . '/chat/completions',
		array(
			'timeout' => 25,
			'headers' => array(
				'Authorization' => 'Bearer ' . $provider['key'],
				'Content-Type'  => 'application/json',
				'HTTP-Referer'  => home_url( '/' ),
				'X-Title'       => 'Toplist Khanh Hoa',
			),
			'body'    => wp_json_encode(
				array(
					'model'       => $provider['model'],
					'messages'    => $messages,
					'temperature' => 0.4,
					'max_tokens'  => 700,
				)
			),
		)
	);
	if ( is_wp_error( $response ) ) {
		return $response;
	}
	$code = (int) wp_remote_retrieve_response_code( $response );
	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	$text = isset( $body['choices'][0]['message']['content'] ) ? trim( (string) $body['choices'][0]['message']['content'] ) : '';
	if ( 200 !== $code || '' === $text ) {
		return new WP_Error( 'tkh_chat_provider', sprintf( '%s HTTP %d', $provider['id'], $code ) );
	}
	// Some reasoning models wrap their scratchpad in <think> tags; never show it.
	return trim( preg_replace( '#<think>.*?</think>#s', '', $text ) );
}

/**
 * Minimal, safe markdown → HTML: escapes everything, then allows **bold**, '- ' lists, paragraphs
 * and [text](url) links to this site only (other links become plain text).
 */
function tkh_chat_render( $markdown ) {
	// Every pattern uses /u: without it \R and \s also match the single byte 0x85, which occurs
	// inside UTF-8 Vietnamese letters (ễ = E1 BB 85) and split words such as "Nguyễn" in half.
	$site   = wp_parse_url( home_url( '/' ), PHP_URL_HOST );
	$lines  = preg_split( '/\R/u', trim( (string) $markdown ) );
	$html   = '';
	$inlist = false;
	$inline = function ( $text ) use ( $site ) {
		$text = esc_html( $text );
		$text = preg_replace_callback(
			'/\[([^\]]+)\]\(([^)\s]+)\)/u',
			function ( $m ) use ( $site ) {
				$url = html_entity_decode( $m[2] );
				if ( wp_parse_url( $url, PHP_URL_HOST ) === $site ) {
					return '<a href="' . esc_url( $url ) . '">' . $m[1] . '</a>';
				}
				return $m[1];
			},
			$text
		);
		return preg_replace( '/\*\*(.+?)\*\*/u', '<strong>$1</strong>', $text );
	};
	foreach ( $lines as $line ) {
		$line = trim( $line );
		if ( preg_match( '/^[-*•]\s+(.*)$/u', $line, $m ) ) {
			if ( ! $inlist ) {
				$html  .= '<ul>';
				$inlist = true;
			}
			$html .= '<li>' . $inline( $m[1] ) . '</li>';
			continue;
		}
		if ( $inlist ) {
			$html  .= '</ul>';
			$inlist = false;
		}
		if ( '' !== $line ) {
			$html .= '<p>' . $inline( preg_replace( '/^#+\s*/u', '', $line ) ) . '</p>';
		}
	}
	return $html . ( $inlist ? '</ul>' : '' );
}

/**
 * Models do not always follow the linking rule. Turn a bold place name that matches a retrieved
 * article (and is not linked yet) into a link to that article, once per article.
 */
function tkh_chat_link_places( $markdown, array $docs ) {
	foreach ( $docs as $doc ) {
		if ( false !== strpos( $markdown, $doc['url'] ) ) {
			$place = $doc['place'] ? $doc['place'] : $doc['title'];
			$url   = preg_quote( $doc['url'], '/' );
			// A link labelled with the full article title reads badly in chat: label it with the place name.
			$markdown = preg_replace( '/\[([^\]]{40,})\]\(' . $url . '\)/u', '[' . $place . '](' . $doc['url'] . ')', $markdown );
			// "**Name** [Name](url)" or "**Name** ([Name](url))" → "**[Name](url)**".
			$markdown = preg_replace( '/\*\*([^*\n]+)\*\*\s*\(?\[([^\]]+)\]\(' . $url . '\)\)?/u', '**[$1](' . $doc['url'] . ')**', $markdown );
			continue;
		}
		$names = array_filter( array_unique( array( tkh_chat_words( $doc['place'] ), tkh_chat_words( preg_replace( '/[:|–—].*$/u', '', $doc['title'] ) ) ) ) );
		$markdown = preg_replace_callback(
			'/\*\*(?!\[)([^*\n]+?)\*\*/u',
			function ( $m ) use ( $names, $doc ) {
				$bold = tkh_chat_words( $m[1] );
				foreach ( $names as $name ) {
					if ( '' !== $name && ( false !== mb_strpos( $bold, $name ) || false !== mb_strpos( $name, $bold ) ) && mb_strlen( $bold ) >= 4 ) {
						return '**[' . $m[1] . '](' . $doc['url'] . ')**';
					}
				}
				return $m[0];
			},
			$markdown,
			1
		);
	}
	return $markdown;
}

function tkh_chat_sources( array $docs ) {
	return array_map(
		fn( $d ) => array( 'title' => $d['place'] ? $d['place'] : $d['title'], 'url' => $d['url'], 'image' => $d['image'], 'rating' => $d['rating'], 'meta' => trim( $d['region'] . ' · ' . $d['price'], ' ·' ) ),
		$docs
	);
}

function tkh_chat_handle( WP_REST_Request $request ) {
	$lang    = 'en' === $request->get_param( 'lang' ) ? 'en' : 'vi';
	$en      = 'en' === $lang;
	$message = trim( wp_strip_all_tags( (string) $request->get_param( 'message' ) ) );
	$message = mb_substr( $message, 0, TKH_CHAT_MAX_CHARS );
	if ( '' === $message ) {
		return new WP_REST_Response( array( 'error' => 'empty' ), 400 );
	}
	if ( ! tkh_chat_rate_ok() ) {
		return new WP_REST_Response(
			array(
				'mode'   => 'limited',
				'html'   => '<p>' . esc_html( $en ? 'You are sending questions a little fast. Please wait a moment and try again.' : 'Bạn đang hỏi hơi nhanh. Vui lòng chờ một chút rồi thử lại nhé.' ) . '</p>',
				'sources' => array(),
			),
			429
		);
	}

	// Short history (last 3 exchanges) keeps follow-up questions in context.
	$history = array();
	foreach ( array_slice( (array) $request->get_param( 'history' ), -6 ) as $turn ) {
		if ( is_array( $turn ) && isset( $turn['role'], $turn['content'] ) && in_array( $turn['role'], array( 'user', 'assistant' ), true ) ) {
			$history[] = array( 'role' => $turn['role'], 'content' => mb_substr( wp_strip_all_tags( (string) $turn['content'] ), 0, 800 ) );
		}
	}

	$cache_key = 'tkh_chat_a3_' . md5( $lang . '|' . tkh_chat_normalize( $message ) );
	if ( ! $history ) {
		$cached = get_transient( $cache_key );
		if ( is_array( $cached ) ) {
			$cached['cached'] = true;
			return new WP_REST_Response( $cached, 200 );
		}
	}

	// Follow-ups ("what about prices there?") retrieve with the previous question too.
	$last_user = '';
	foreach ( array_reverse( $history ) as $turn ) {
		if ( 'user' === $turn['role'] ) {
			$last_user = $turn['content'];
			break;
		}
	}
	$docs = tkh_chat_retrieve( $message . ' ' . $last_user, $lang );

	$messages = array_merge(
		array( array( 'role' => 'system', 'content' => tkh_chat_system_prompt( $lang, $docs ) ) ),
		$history,
		array( array( 'role' => 'user', 'content' => $message ) )
	);
	foreach ( tkh_chat_providers() as $provider ) {
		$answer = tkh_chat_call_provider( $provider, $messages );
		if ( ! is_wp_error( $answer ) ) {
			$answer = tkh_chat_link_places( $answer, $docs );
			$result = array( 'mode' => 'ai', 'provider' => $provider['label'], 'html' => tkh_chat_render( $answer ), 'text' => $answer, 'sources' => tkh_chat_sources( $docs ) );
			if ( ! $history ) {
				set_transient( $cache_key, $result, TKH_CHAT_CACHE_HOURS * HOUR_IN_SECONDS );
			}
			return new WP_REST_Response( $result, 200 );
		}
	}

	// Search mode: no provider configured or all out of quota.
	if ( ! $docs ) {
		$docs = array_slice( tkh_chat_index( $lang ), 0, 3 );
		$intro = $en ? 'I could not find an exact match, but these popular places may help:' : 'Mình chưa tìm thấy bài khớp hẳn, bạn tham khảo vài địa điểm nổi bật nhé:';
	} else {
		$intro = $en ? 'Here are the most relevant articles on our site:' : 'Đây là những bài viết phù hợp nhất trên site:';
	}
	return new WP_REST_Response( array( 'mode' => 'search', 'html' => '<p>' . esc_html( $intro ) . '</p>', 'text' => $intro, 'sources' => tkh_chat_sources( $docs ) ), 200 );
}
