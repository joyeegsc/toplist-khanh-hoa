<?php
/**
 * Chat widget markup (launcher + panel). Behaviour lives in assets/js/chat.js.
 */

defined( 'ABSPATH' ) || exit;
?>
<button type="button" id="tkh-chat-launcher" class="fixed right-4 bottom-24 lg:right-6 lg:bottom-6 z-[45] h-14 pl-4 pr-5 rounded-full bg-gradient-to-br from-primary-container to-deep-ocean text-on-primary shadow-[0_12px_32px_-4px_rgba(2,62,138,0.45)] flex items-center gap-2 cursor-pointer hover:brightness-110 transition-all focus-visible:ring-4 focus-visible:ring-primary-fixed-dim" aria-controls="tkh-chat" aria-expanded="false">
	<span class="material-symbols-outlined icon-fill" aria-hidden="true">forum</span>
	<span class="font-label-lg text-label-lg hidden sm:inline"><?php esc_html_e( 'Hỏi trợ lý', 'toplist-khanh-hoa' ); ?></span>
	<span class="sr-only sm:hidden"><?php esc_html_e( 'Hỏi trợ lý', 'toplist-khanh-hoa' ); ?></span>
</button>

<section id="tkh-chat" class="fixed z-[65] inset-x-0 bottom-0 h-[88dvh] sm:inset-x-auto sm:right-6 sm:bottom-24 sm:w-[400px] sm:h-[600px] sm:max-h-[calc(100dvh-8rem)] flex flex-col bg-surface-container-lowest rounded-t-3xl sm:rounded-3xl shadow-[0_20px_48px_-8px_rgba(23,35,58,0.3)] border border-slate-border/70 overflow-hidden" role="dialog" aria-modal="false" aria-labelledby="tkh-chat-title" hidden>
	<header class="relative shrink-0 px-5 py-4 bg-gradient-to-r from-[#005d90] via-[#0077b6] to-[#023e8a] text-on-primary">
		<div class="flex items-center gap-3 pr-10">
			<span class="w-10 h-10 shrink-0 rounded-full bg-white/15 flex items-center justify-center"><span class="material-symbols-outlined icon-fill text-sunset-gold" aria-hidden="true">auto_awesome</span></span>
			<div>
				<h2 id="tkh-chat-title" class="font-headline-sm text-headline-sm"><?php esc_html_e( 'Trợ lý du lịch Khánh Hòa', 'toplist-khanh-hoa' ); ?></h2>
				<p class="font-body-sm text-body-sm text-white/80"><?php esc_html_e( 'Gợi ý dựa trên các bài review của site', 'toplist-khanh-hoa' ); ?></p>
			</div>
		</div>
		<button type="button" class="absolute right-3 top-3 w-11 h-11 rounded-full hover:bg-white/15 flex items-center justify-center cursor-pointer" data-chat-close aria-label="<?php esc_attr_e( 'Đóng trợ lý', 'toplist-khanh-hoa' ); ?>"><span class="material-symbols-outlined" aria-hidden="true">close</span></button>
	</header>

	<div class="flex-1 overflow-y-auto px-4 py-4 space-y-3 bg-surface-porcelain" data-chat-log aria-live="polite" aria-relevant="additions"></div>

	<div class="shrink-0 px-4 pt-2 flex gap-2 overflow-x-auto no-scrollbar" data-chat-suggestions role="group" aria-label="<?php esc_attr_e( 'Câu hỏi gợi ý', 'toplist-khanh-hoa' ); ?>"></div>

	<form class="shrink-0 p-3 flex items-end gap-2" data-chat-form>
		<label class="sr-only" for="tkh-chat-input"><?php esc_html_e( 'Nhập câu hỏi', 'toplist-khanh-hoa' ); ?></label>
		<textarea id="tkh-chat-input" rows="1" maxlength="500" class="flex-1 resize-none max-h-32 min-h-11 px-4 py-2.5 rounded-2xl bg-surface-mist border border-slate-border focus:outline-none focus:ring-2 focus:ring-primary-container/40 font-body-md text-body-md text-on-surface placeholder:text-slate-muted" placeholder="<?php esc_attr_e( 'Hỏi về ăn uống, chỗ ở, lịch trình...', 'toplist-khanh-hoa' ); ?>"></textarea>
		<button type="submit" class="w-11 h-11 shrink-0 rounded-full bg-gradient-to-r from-[#FF7A00] to-sunset-gold text-on-primary flex items-center justify-center cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed transition-opacity" aria-label="<?php esc_attr_e( 'Gửi', 'toplist-khanh-hoa' ); ?>" data-chat-send><span class="material-symbols-outlined" aria-hidden="true">send</span></button>
	</form>
	<p class="shrink-0 px-4 pb-3 font-body-sm text-[11px] leading-4 text-slate-muted"><?php esc_html_e( 'Trợ lý AI có thể nhầm lẫn, hãy kiểm tra giá và giờ mở cửa trước khi đi. Đừng nhập thông tin cá nhân.', 'toplist-khanh-hoa' ); ?></p>
</section>
