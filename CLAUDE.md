# Hướng dẫn cho AI Agents (Opus / Claude & Argon / Gemini) — Dự án `Pccc24-WP`

1. **Kênh giao tiếp bắt buộc:** Đọc và phản hồi trực tiếp trong file [`Chat.md`](Chat.md).
2. **Mục tiêu của Chủ dự án:**
   - Dịch ngược toàn bộ website `https://pccc24.com` (sử dụng [`https://github.com/morluto/rea`](https://github.com/morluto/rea) kết hợp dữ liệu & mã nguồn gốc sẵn có tại `D:\Code\PCCC24` + DB MySQL `pccc24_db`) và chuyển đổi hoàn chỉnh sang nền tảng **WordPress** tại `D:\Code\Pccc24-WP`.
   - **Tự động hóa 100% (Zero Manual Touch):** Tự cài đặt mọi repo/công cụ, tự viết code, tự migrate dữ liệu và tự kiểm thử như người thật trên trình duyệt (Playwright / `agent-browser`). Chủ dự án không phải thao tác tay bất kỳ bước nào.
3. **Quy tắc Hẹn giờ Động (Dynamic ETA — Không set cứng 5 phút):**
   - Xem 2 dòng cuối của [`Chat.md`](Chat.md):
     - `<!-- WORK_STATUS: ... | ACTIVE_BY: ... | STARTED_AT: ... | ETA_MINUTES: ... | NOTE: ... -->`
     - `<!-- DEBATE_STATUS: WAITING_FOR_OPUS | ROUND: N -->` (hoặc `WAITING_FOR_ARGON` / `CONSENSUS_REACHED`)
   - Khi bắt đầu làm một tác vụ dài, cập nhật ngay `WORK_STATUS: IN_PROGRESS` kèm `ETA_MINUTES` thực tế.
   - Khi hoàn thành lượt, thêm tin nhắn `## 💬 [#N] ...` ngay trên 2 dòng cuối, ước lượng `ETA_MINUTES` phù hợp với độ dài công việc của lượt tiếp theo cho AI kia, và đổi `DEBATE_STATUS` sang tên AI kia.
   - Tuyệt đối không đặt lịch hẹn giờ cứng 5 phút khi công việc thực tế dài hơn 5 phút.
