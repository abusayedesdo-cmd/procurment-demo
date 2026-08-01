# Word থেকে PDF-এ পরিবর্তন (RFQ, Tender Schedule, Tender Opening)

## কেন বদলানো হলো
PHPWord দিয়ে বানানো `.docx` ফাইল বারবার "Word খুলতে পারছে না" এরর দিচ্ছিল। কারণ খুঁজে বের করলেও (invalid list-style reference) সেটা ঠিক করার পরও risk থেকে যায়, যেহেতু Word-এর internal XML format অনেক strict। তাই আরও সহজ, নির্ভরযোগ্য পথ — **DomPDF** দিয়ে সরাসরি HTML (Blade view) থেকে PDF বানানো। PDF যেকোনো ডিভাইসে ঝামেলা ছাড়াই খোলে, প্রিন্টও করা যায়।

## ⚠️ প্রথম ধাপ — নতুন library
লোকাল মেশিনে:
```powershell
composer require barryvdh/laravel-dompdf
```
এটা আগের `phpoffice/phpword`-এর সাথে conflict করবে না, দুটোই থাকতে পারে (চাইলে phpword বাদও দিতে পারেন, নিচে দেখুন)।

## এই zip-এ যা আছে
```
app/Http/Controllers/Api/DocumentDownloadController.php  — replace, এখন PHPWord-এর বদলে DomPDF ব্যবহার করে
resources/views/documents/layout.blade.php                — নতুন, শেয়ার্ড print-friendly CSS
resources/views/documents/rfq.blade.php                    — নতুন
resources/views/documents/tender-schedule.blade.php        — নতুন
resources/views/documents/tender-opening.blade.php         — নতুন
```

## বসানোর ধাপ
1. `composer require barryvdh/laravel-dompdf` (লোকালে) → deploy
2. উপরের ফাইলগুলো যথাস্থানে কপি করুন (Controller replace, ৪টা নতুন Blade view)
3. `php artisan optimize:clear`
4. `routes/api.php` অপরিবর্তিত থাকবে — একই URL (`/api/rfqs/{id}/document` ইত্যাদি), শুধু এখন `.pdf` ডাউনলোড হবে

## ঐচ্ছিক পরিষ্কার
`app/Services/DocxTemplates/` ফোল্ডারের ৩টা পুরনো PHPWord builder ফাইল এখন আর ব্যবহার হচ্ছে না — চাইলে ডিলিট করে দিতে পারেন, অথবা রেখে দিলেও কোনো সমস্যা নেই (কোনো route এদের আর কল করে না)।

## যা অপরিবর্তিত আছে
ডেটা সোর্স আগের মতোই — PR items → category grouping, Central Procurement Committee সদস্য, RFQ-এর quotations থেকে bidder list, vendor_documents থেকে Trade License/TIN/BIN checklist। শুধু output format বদলেছে, ডেটা লজিক একই।

## পরীক্ষা করুন
বসানোর পর `/modules/rfqs` থেকে "RFQ ডাউনলোড" ক্লিক করুন — এবার সরাসরি PDF ডাউনলোড হয়ে যেকোনো PDF viewer/browser-এ খুলে যাওয়ার কথা। কোনো layout/স্পেসিং সমস্যা দেখলে জানাবেন, CSS ঠিক করে দেব।
