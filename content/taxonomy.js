// Categories, regions (khu_vuc) and tags created by the demo import, with English translations.
// The Vietnamese slug is the stable key the theme uses (navigation order, icons, schema type).

export const categories = [
  {
    slug: 'am-thuc', name: 'Ẩm Thực', description: 'Quán ngon, đặc sản và nhà hàng hải sản được đánh giá cao ở Nha Trang và khắp Khánh Hòa.',
    en: { slug: 'food', name: 'Food', description: 'Well-loved local eateries, regional specialties and seafood restaurants in Nha Trang and across Khanh Hoa.' },
  },
  {
    slug: 'du-lich', name: 'Du Lịch', description: 'Đảo, vịnh, bãi biển và di tích đáng ghé ở Khánh Hòa kèm kinh nghiệm đi lại, chi phí và thời điểm đẹp.',
    en: { slug: 'travel', name: 'Travel', description: 'Islands, bays, beaches and heritage sites in Khanh Hoa, with practical tips on getting there, costs and the best time to go.' },
  },
  {
    slug: 'luu-tru', name: 'Lưu Trú', description: 'Resort, khách sạn và biệt thự nghỉ dưỡng từ Bắc Nha Trang đến Bãi Dài Cam Ranh.',
    en: { slug: 'stays', name: 'Stays', description: 'Resorts, hotels and pool villas from North Nha Trang to Cam Ranh’s Long Beach.' },
  },
  {
    slug: 'giai-tri', name: 'Giải Trí', description: 'Công viên chủ đề, tắm bùn khoáng nóng, beach club và những cuộc vui về đêm ở phố biển.',
    en: { slug: 'entertainment', name: 'Entertainment', description: 'Theme parks, hot-spring mud baths, beach clubs and nightlife in the coastal city.' },
  },
  {
    slug: 'cafe', name: 'Cafe', description: 'Quán cà phê view biển, không gian chill và góc check-in đẹp ở Nha Trang.',
    en: { slug: 'cafes', name: 'Cafés', description: 'Sea-view coffee shops, relaxed hangouts and photogenic cafés in Nha Trang.' },
  },
];

export const regions = [
  {
    slug: 'nha-trang', name: 'Nha Trang', description: 'Trung tâm phố biển với bãi Trần Phú, các đảo trong vịnh và khu ẩm thực sầm uất.',
    en: { slug: 'nha-trang-city', name: 'Nha Trang', description: 'The coastal city centre: Tran Phu beach, the bay islands and a lively food scene.' },
  },
  {
    slug: 'bac-nha-trang', name: 'Bắc Nha Trang', description: 'Bờ bắc sông Cái: di tích Tháp Bà, Hòn Chồng và dải resort dọc đường Phạm Văn Đồng.',
    en: { slug: 'north-nha-trang', name: 'North Nha Trang', description: 'North of the Cai River: Po Nagar towers, Hon Chong and the resort strip along Pham Van Dong road.' },
  },
  {
    slug: 'cam-ranh', name: 'Cam Ranh', description: 'Vịnh Cam Ranh, cảng Ba Ngòi và các đảo tôm hùm Bình Ba, Bình Hưng.',
    en: { slug: 'cam-ranh-area', name: 'Cam Ranh', description: 'Cam Ranh Bay, Ba Ngoi port and the lobster islands of Binh Ba and Binh Hung.' },
  },
  {
    slug: 'cam-lam', name: 'Cam Lâm', description: 'Bãi Dài trải dài gần sân bay Cam Ranh với các khu nghỉ dưỡng biệt lập.',
    en: { slug: 'cam-lam-area', name: 'Cam Lam', description: 'Long Beach (Bai Dai) near Cam Ranh airport, lined with secluded resorts.' },
  },
  {
    slug: 'ninh-hoa', name: 'Ninh Hòa', description: 'Biển Dốc Lết cát trắng, đầm Nha Phu và những cánh đồng muối ven biển.',
    en: { slug: 'ninh-hoa-area', name: 'Ninh Hoa', description: 'White-sand Doc Let beach, Nha Phu lagoon and coastal salt fields.' },
  },
  {
    slug: 'van-ninh', name: 'Vạn Ninh', description: 'Vịnh Vân Phong hoang sơ, đảo Điệp Sơn và cực Đông Mũi Đôi.',
    en: { slug: 'van-ninh-area', name: 'Van Ninh', description: 'Unspoilt Van Phong Bay, Diep Son island and Mui Doi, Vietnam’s easternmost point.' },
  },
  {
    slug: 'phan-rang-vinh-hy', name: 'Phan Rang & Vĩnh Hy', description: 'Vùng đất nắng gió phía nam tỉnh: vịnh Vĩnh Hy, vườn nho và tháp Chăm.',
    en: { slug: 'phan-rang-vinh-hy-area', name: 'Phan Rang & Vinh Hy', description: 'The sunny, windswept south of the province: Vinh Hy Bay, vineyards and Cham towers.' },
  },
];

export const tags = [
  { slug: 'noi-bat', name: 'Nổi bật', en: { slug: 'featured', name: 'Featured' } },
  { slug: 'ven-bien', name: 'Ven biển', en: { slug: 'beachfront', name: 'Beachfront' } },
  { slug: 'hoang-hon', name: 'Ngắm hoàng hôn', en: { slug: 'sunset-views', name: 'Sunset views' } },
  { slug: 'binh-minh', name: 'Ngắm bình minh', en: { slug: 'sunrise-views', name: 'Sunrise views' } },
  { slug: 'di-tich', name: 'Di tích lịch sử', en: { slug: 'historic-sites', name: 'Historic sites' } },
  { slug: 'dac-san', name: 'Đặc sản địa phương', en: { slug: 'local-specialties', name: 'Local specialties' } },
  { slug: 'gia-dinh', name: 'Hợp gia đình', en: { slug: 'family-friendly', name: 'Family-friendly' } },
  { slug: 'check-in', name: 'Check-in đẹp', en: { slug: 'photo-spots', name: 'Photo spots' } },
  { slug: 'lan-bien', name: 'Lặn biển', en: { slug: 'snorkeling-diving', name: 'Snorkeling & diving' } },
  { slug: 'nghi-duong', name: 'Nghỉ dưỡng', en: { slug: 'resorts-retreats', name: 'Resorts & retreats' } },
];
