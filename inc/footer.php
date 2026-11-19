</div> <footer class="bg-[#0f172a] text-slate-400 border-t border-slate-800 mt-auto font-sans relative z-10">
    <div class="container mx-auto px-4 py-16">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
            
            <div>
                <a href="index.php" class="flex items-center gap-2 mb-6 group">
                    <div class="w-10 h-10 bg-indigo-600 text-white rounded-xl flex items-center justify-center shadow-lg shadow-indigo-900/20">
                        <i class="fa-solid fa-code text-lg"></i>
                    </div>
                    <span class="text-2xl font-bold text-white tracking-tight">Yazılım<span class="text-indigo-500">Market</span></span>
                </a>
                <p class="text-slate-400 text-sm leading-relaxed mb-6">
                    Projelerinizi hızlandıracak profesyonel PHP scriptleri ve temaları en uygun fiyatlarla, anında teslimat garantisiyle sunuyoruz.
                </p>
                <div class="flex gap-4">
                    <a href="#" class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-white hover:bg-indigo-600 transition"><i class="fa-brands fa-twitter"></i></a>
                    <a href="#" class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-white hover:bg-indigo-600 transition"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-white hover:bg-indigo-600 transition"><i class="fa-brands fa-whatsapp"></i></a>
                </div>
            </div>

            <div>
                <h3 class="text-white font-bold text-lg mb-6">Kurumsal</h3>
                <ul class="space-y-3 text-sm">
                    <li><a href="#" class="hover:text-indigo-400 transition flex items-center gap-2"><i class="fa-solid fa-angle-right text-xs"></i> Hakkımızda</a></li>
                    <li><a href="market.php" class="hover:text-indigo-400 transition flex items-center gap-2"><i class="fa-solid fa-angle-right text-xs"></i> Tüm Ürünler</a></li>
                    <li><a href="#" class="hover:text-indigo-400 transition flex items-center gap-2"><i class="fa-solid fa-angle-right text-xs"></i> Satıcı Başvurusu</a></li>
                    <li><a href="#" class="hover:text-indigo-400 transition flex items-center gap-2"><i class="fa-solid fa-angle-right text-xs"></i> İletişim</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-white font-bold text-lg mb-6">Müşteri İlişkileri</h3>
                <ul class="space-y-3 text-sm">
                    <li><a href="destek.php" class="hover:text-indigo-400 transition flex items-center gap-2"><i class="fa-solid fa-angle-right text-xs"></i> Destek Merkezi</a></li>
                    <li><a href="hesap-ayarlari.php" class="hover:text-indigo-400 transition flex items-center gap-2"><i class="fa-solid fa-angle-right text-xs"></i> Hesap Ayarları</a></li>
                    <li><a href="#" class="hover:text-indigo-400 transition flex items-center gap-2"><i class="fa-solid fa-angle-right text-xs"></i> Gizlilik Politikası</a></li>
                    <li><a href="#" class="hover:text-indigo-400 transition flex items-center gap-2"><i class="fa-solid fa-angle-right text-xs"></i> İade Şartları</a></li>
                </ul>
            </div>

            

        </div>
    </div>

    <div class="border-t border-slate-800 bg-[#020617]">
        <div class="container mx-auto px-4 py-6 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-xs text-slate-500 text-center md:text-left">
                &copy; <?php echo date("Y"); ?> <span class="text-indigo-500 font-bold">YazılımMarket</span>. Tüm hakları saklıdır.
            </p>
            <div class="flex items-center gap-4 text-slate-400 text-2xl opacity-70 grayscale hover:grayscale-0 transition duration-500">
                <i class="fa-brands fa-cc-visa hover:text-white"></i>
                <i class="fa-brands fa-cc-mastercard hover:text-white"></i>
                <i class="fa-brands fa-cc-amex hover:text-white"></i>
                <i class="fa-brands fa-bitcoin hover:text-white"></i>
            </div>
        </div>
    </div>
</footer>

<?php if (isset($settings['popup_status']) && $settings['popup_status'] == 1): ?>
<div id="marketingPopup" class="fixed inset-0 z-[100] flex items-center justify-center px-4 opacity-0 pointer-events-none transition-opacity duration-500">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closePopup()"></div>
    
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl relative z-10 overflow-hidden flex flex-col md:flex-row transform scale-95 transition-transform duration-500" id="popupContent">
        <button onclick="closePopup()" class="absolute top-4 right-4 bg-black/10 hover:bg-black/20 text-gray-600 hover:text-gray-900 rounded-full w-8 h-8 flex items-center justify-center transition z-20">
            <i class="fa-solid fa-times"></i>
        </button>

        <?php if(!empty($settings['popup_image'])): ?>
        <div class="w-full md:w-1/2 h-48 md:h-auto relative">
            <img src="<?php echo htmlspecialchars($settings['popup_image']); ?>" class="w-full h-full object-cover absolute inset-0">
        </div>
        <?php endif; ?>

        <div class="w-full <?php echo !empty($settings['popup_image']) ? 'md:w-1/2' : 'w-full'; ?> p-10 flex flex-col justify-center text-center md:text-left">
            <h3 class="text-3xl font-extrabold text-slate-900 mb-4"><?php echo htmlspecialchars($settings['popup_title']); ?></h3>
            <div class="text-slate-600 mb-8 text-lg leading-relaxed"><?php echo htmlspecialchars($settings['popup_text']); ?></div> <?php if(!empty($settings['popup_link'])): ?>
            <a href="<?php echo htmlspecialchars($settings['popup_link']); ?>" class="bg-indigo-600 text-white px-8 py-4 rounded-xl font-bold hover:bg-indigo-700 transition shadow-xl shadow-indigo-500/30 text-center block w-full">
                Fırsatı Yakala
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Çerez kontrolü: Daha önce kapatılmadıysa göster
        if (!localStorage.getItem('popupClosed_v1')) {
            setTimeout(function() {
                const popup = document.getElementById('marketingPopup');
                const content = document.getElementById('popupContent');
                if(popup) {
                    popup.classList.remove('opacity-0', 'pointer-events-none');
                    content.classList.remove('scale-95');
                    content.classList.add('scale-100');
                }
            }, 2500); // 2.5 sn sonra açılır
        }
    });

    function closePopup() {
        const popup = document.getElementById('marketingPopup');
        if(popup) {
            popup.classList.add('opacity-0', 'pointer-events-none');
            // Tarayıcıya kaydet
            localStorage.setItem('popupClosed_v1', 'true'); 
        }
    }
</script>
<?php endif; ?>

<script>
    const Toast = Swal.mixin({
        toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true,
        background: '#fff', color: '#1e293b',
        didOpen: (toast) => { toast.addEventListener('mouseenter', Swal.stopTimer); toast.addEventListener('mouseleave', Swal.resumeTimer); }
    });

    function addToCart(productId) {
        $.ajax({
            url: 'ajax-sepet.php', type: 'POST', data: { product_id: productId },
            success: function(response) {
                if(response.status === 'success') { Toast.fire({ icon: 'success', title: response.message }); } 
                else if(response.status === 'info') { Toast.fire({ icon: 'info', title: response.message }); } 
                else { Toast.fire({ icon: 'error', title: 'Bir hata oluştu.' }); }
                updateCartCount(response.count);
            },
            error: function() { Toast.fire({ icon: 'error', title: 'Sunucu hatası!' }); }
        });
    }

    function updateCartCount(count) {
        let cartLink = $('a[href="sepet.php"]');
        let badge = cartLink.find('span.absolute'); // Badge'i bul
        
        if (count > 0) {
            if (badge.length > 0) {
                badge.text(count);
            } else {
                // Badge yoksa oluştur (Sepet ikonunun içine ekle)
                cartLink.find('.relative').append('<span class="absolute -top-2 -right-2 bg-rose-500 text-white text-[10px] font-bold h-4 w-4 rounded-full flex items-center justify-center ring-2 ring-white">' + count + '</span>');
            }
        }
    }
</script>

</body>
</html>