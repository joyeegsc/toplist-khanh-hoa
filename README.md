# Toplist Khánh Hòa — Website review du lịch WordPress song ngữ

Website cẩm nang du lịch Khánh Hòa: thiết kế bằng Stitch ("Nha Trang Riviera & Coastal Luxe"),
dựng thành theme WordPress có giao diện giống thiết kế, song ngữ Việt – Anh, chuẩn SEO, có
dữ liệu mẫu đầy đủ, lọc và tìm kiếm. Chạy live miễn phí trên Render + Supabase + GitHub.

| Yêu cầu | Cách đáp ứng |
|---|---|
| Thiết kế bằng Stitch → WordPress giống thiết kế | Theme `theme/toplist-khanh-hoa` dùng đúng design tokens của mẫu (`tailwind.config.js`) |
| Đa ngôn ngữ Anh – Việt | Plugin Polylang; tiếng Việt ở `/`, tiếng Anh ở `/en/`; giao diện dịch qua `languages/en_US.l10n.php` |
| Chuẩn SEO | `inc/seo.php`: title, meta description, canonical, robots, Open Graph/Twitter, JSON-LD, hreflang, sitemap |
| Dữ liệu mẫu đầy đủ | 20 bài review × 2 ngôn ngữ, 7 khu vực, 5 chuyên mục, 10 thẻ, 6 trang tĩnh |
| Lọc và tìm kiếm | Lọc theo chuyên mục, khu vực, điểm, mức giá, sắp xếp; tìm kiếm toàn site |
| Live không mua hosting/tên miền | Docker trên Render Free + sao lưu Supabase Storage + GitHub, xem [docs/deploy-render-supabase.md](docs/deploy-render-supabase.md) |

## Tính năng

- **Trang chủ** theo đúng mẫu: hero kèm ô tìm kiếm, dải điểm đến nổi bật, thống kê, ẩm thực, khám phá theo khu vực, lưu trú, đăng ký bản tin.
- **Danh sách** (chuyên mục / khu vực / thẻ / tìm kiếm): lọc khu vực, điểm đánh giá, phân khúc giá, sắp xếp. Lọc phía server nên chạy được cả khi tắt JavaScript.
- **Bài viết:** mục lục tự động, thời gian đọc, khung thông tin nhanh, bản đồ Google nhúng, nút chỉ đường, chia sẻ, lưu yêu thích, bài liên quan.
- **Yêu thích:** lưu trên trình duyệt (localStorage), có trang riêng.
- **Bản tin:** lưu email, xem ở *Công cụ → Đăng ký nhận tin*.
- **Trợ lý du lịch (chatbot):** trả lời song ngữ dựa trên các bài của site, dùng chuỗi AI miễn phí (Gemini → Groq → OpenRouter → OrcaRouter); hết lượt thì gợi ý bài phù hợp. Xem [docs/chatbot.md](docs/chatbot.md).
- **Di động:** thanh điều hướng đáy màn hình, menu trượt, chuyển ngôn ngữ.

## SEO trong theme

- Title theo ngữ cảnh ("Du Lịch Khánh Hòa | …", "Where to Eat & What to Do in Nha Trang | …").
- Meta description từ tóm tắt bài / mô tả chuyên mục.
- Canonical cho mọi trang danh sách; trang lọc, trang tìm kiếm và trang Yêu thích đặt `noindex, follow`.
- Open Graph và Twitter Card (ảnh, locale `vi_VN` / `en_US` kèm alternate).
- JSON-LD gồm `Organization`, `WebSite` + `SearchAction`, `BreadcrumbList`, `Article` + địa điểm (`TouristAttraction` / `Restaurant` / `Hotel` / `EntertainmentBusiness` / `CafeOrCoffeeShop`), `CollectionPage` + `ItemList`.
- hreflang VI ↔ EN (Polylang); sitemap tách theo ngôn ngữ, bỏ sitemap người dùng và trang tiện ích.
- Trang tác giả và trang lưu trữ theo ngày chuyển hướng 301 về trang chủ.
- Ô nhập mã xác minh Google/Bing (*Tùy biến → SEO*).
- Hiệu năng: font icon chỉ tải đúng các icon dùng đến, ảnh lazy-load có kích thước, CSS đã nén, cache tĩnh 1 năm (Apache).
- Nếu cài Yoast / Rank Math / SEOPress / AIOSEO, phần SEO của theme tự tắt để không trùng thẻ.

Theme **không** xuất `aggregateRating`, vì điểm hiển thị là số tham khảo từ nền tảng bản đồ, mà
Google không cho dùng đánh giá của bên thứ ba làm rich snippet.

## Cấu trúc

```
theme/toplist-khanh-hoa/   theme WordPress (PHP), copy vào wp-content/themes
  inc/                     i18n, SEO, bộ lọc, trường địa điểm, nhập dữ liệu mẫu, bản tin
  demo/content.json        dữ liệu mẫu song ngữ (sinh tự động)
  languages/en_US.l10n.php bản dịch giao diện (sinh tự động)
content/                   nội dung gốc: bài VI, bản EN (content/en), chuyên mục/khu vực/thẻ, chuỗi giao diện EN
scripts/                   build dữ liệu + bản dịch, đóng gói zip
src/styles.css             nguồn Tailwind
Dockerfile, docker/, render.yaml, .github/workflows/   deploy Render + Supabase + GitHub
```

## Chạy trên máy

Cần Node.js 18+.

```bash
npm install
npm run dev
```

`npm run dev` build CSS + dữ liệu + bản dịch, rồi chạy WordPress Playground tại http://127.0.0.1:9400:
cài Polylang, kích hoạt theme, nhập dữ liệu mẫu song ngữ. Không cần XAMPP/MySQL. Đăng nhập local:
`admin` / `khanhhoa-local-dev` (chỉ dùng trên máy). Mỗi lần chạy là một site mới.

## Cài lên WordPress có sẵn (hosting khác)

1. `npm run build` → tải `dist/toplist-khanh-hoa.zip` lên *Giao diện → Thêm mới → Tải lên* và kích hoạt.
2. Cài và kích hoạt plugin **Polylang**.
3. *Công cụ → Dữ liệu mẫu → Nhập dữ liệu mẫu*. Bước này tạo ngôn ngữ VI/EN, chuyên mục, khu vực, 40 bài và 12 trang.

## Lưu ý nội dung

Bài viết được viết mới hoàn toàn (tiêu đề, slug, bố cục, câu chữ), chỉ dùng chung dữ kiện công khai
(tên, địa chỉ, khoảng giá) với các bài tương ứng trên toplistkhanhhoa.com. **Điểm đánh giá, giá vé,
giờ mở cửa là số tham khảo, cần kiểm tra lại trước khi xuất bản.** Ảnh minh họa đang dùng tạm ảnh
của bản thiết kế Stitch; nên thay bằng ảnh thật có bản quyền (đặt *Ảnh đại diện* cho từng bài).
