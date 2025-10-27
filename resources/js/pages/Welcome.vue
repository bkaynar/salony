<script setup lang="ts">
import { Button } from '@/components/ui/button'
import {
    Check,
    ChevronRight,
    Clapperboard,
    Clock,
    Cloud,
    Contact,
    Cpu,
    Home,
    MessageCircle,
    Palette,
    Play,
    Sparkles,
    ThumbsUp,
    User,
    Users,
    Zap,
} from 'lucide-vue-next'
import { ref } from 'vue'

const faqs = ref([
    {
        question: 'Randevu hatırlatmaları nasıl çalışır?',
        answer: 'Randevudan 24 saat ve 2 saat önce müşterilerinize otomatik olarak SMS veya WhatsApp mesajları gönderilir. Bu sayede "unutulan" randevuların önüne geçilir.',
        open: false,
    },
    {
        question: 'Müşteri bilgilerim güvende mi?',
        answer: 'Evet, tüm verileriniz bulut tabanlı sunucularda güvenli bir şekilde saklanır ve yedeklenir. Verilerinize yalnızca siz ve yetkilendirdiğiniz personeliniz erişebilir.',
        open: false,
    },
    {
        question: 'Kurulum için teknik bilgiye ihtiyacım var mı?',
        answer: 'Hayır, e-makas’ı kullanmak için herhangi bir teknik bilgiye veya kuruluma ihtiyacınız yok. Sadece birkaç dakika içinde salonunuzu kaydedip kullanmaya başlayabilirsiniz.',
        open: false,
    },
    {
        question: 'İstediğim zaman iptal edebilir miyim?',
        answer: 'Evet, aboneliğinizi hiçbir taahhüt olmadan istediğiniz zaman iptal edebilirsiniz. Gizli ücretler veya uzun vadeli sözleşmeler yoktur.',
        open: false,
    },
])
</script>

<template>
    <div class="bg-gray-50 text-gray-800 scroll-smooth">
        <!-- Header -->
        <header class="bg-white shadow-sm sticky top-0 z-40">
            <div class="container mx-auto px-4 py-4 flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <Zap class="h-8 w-8 text-blue-600" />
                    <span class="text-2xl font-bold text-gray-900">e-makas</span>
                </div>
                <nav class="hidden md:flex items-center gap-6">
                    <a href="#features" class="text-gray-600 hover:text-blue-600 transition-colors">Özellikler</a>
                    <a href="#pricing" class="text-gray-600 hover:text-blue-600 transition-colors">Fiyatlandırma</a>
                    <a href="#testimonials" class="text-gray-600 hover:text-blue-600 transition-colors">Yorumlar</a>
                </nav>
                <div class="flex items-center gap-2">
                    <Button variant="ghost">Giriş Yap</Button>
                    <Button>14 Gün Ücretsiz Dene</Button>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <main class="container mx-auto px-4 mt-16 text-center">
            <div class="bg-blue-600 text-white rounded-full px-4 py-1 inline-block text-sm font-semibold">
                Yeni Nesil Randevu Yönetimi
            </div>
            <h1 class="text-5xl md:text-7xl font-extrabold mt-4 text-gray-900">Defteri Kapatın, Salonunuzu Büyütün.</h1>
            <h2 class="text-lg md:text-xl text-gray-600 max-w-3xl mx-auto mt-6">
                Kuaförler, berberler ve güzellik merkezleri için geliştirilmiş yeni nesil randevu yönetim sistemi. İşinize
                odaklanın, randevularınızı <span class="font-semibold text-blue-600">e-makas</span> takip
                etsin.
            </h2>
            <div class="mt-8 flex justify-center items-center gap-4">
                <Button size="lg" class="text-lg">14 Gün Ücretsiz Deneyin</Button>

                <!-- hidden checkbox used as a peer for showing the modal (no script changes needed) -->
                <input id="video-modal-toggle" type="checkbox" class="peer hidden" />

                <!-- label acts as the "open modal" button -->
                <label
                    for="video-modal-toggle"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-md border bg-white text-gray-800 hover:bg-gray-50 cursor-pointer"
                >
                    <Play class="h-5 w-5" />
                    Tanıtımı İzle
                </label>

                <!-- Modal (hidden by default, shown when peer is checked) -->
                <div
                    class="hidden peer-checked:flex fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50 p-4"
                    aria-hidden="true"
                >
                    <div class="relative bg-white rounded-lg overflow-hidden max-w-3xl w-full">
                        <!-- click this label to close (tied to the same checkbox) -->
                        <label for="video-modal-toggle" class="absolute right-3 top-3 z-20 inline-flex items-center justify-center w-8 h-8 rounded-full bg-white text-gray-700 hover:bg-gray-100 cursor-pointer">
                            ✕
                        </label>

                        <!-- 16:9 responsive iframe -->
                        <div class="relative pb-[56.25%]">
                            <iframe
                                class="absolute inset-0 w-full h-full"
                                src="https://www.youtube.com/embed/VIDEO_ID?autoplay=1"
                                title="Tanıtım Videosu"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                            ></iframe>
                        </div>
                    </div>
                </div>
            </div>
            <p class="mt-4 text-sm text-gray-500">Kredi kartı gerekmez. Kurulum yok.</p>
        </main>

        <!-- Problem Section -->
        <section class="py-20 sm:py-32">
            <div class="container mx-auto px-4">
                <div class="text-center">
                    <h2 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
                        Telefonla Randevu Takibi Artık Yorucu Geliyorsa...
                    </h2>
                    <p class="mt-4 text-lg text-gray-600">
                        İşletmenizi büyütmenin önündeki bu engelleri kaldırın.
                    </p>
                </div>
                <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="bg-white p-8 rounded-xl shadow-lg">
                        <div
                            class="bg-red-100 text-red-600 rounded-full h-12 w-12 flex items-center justify-center"
                        >
                            <User class="h-6 w-6" />
                        </div>
                        <h3 class="mt-6 text-xl font-bold text-gray-900">Müşteri Kaçırıyorsunuz</h3>
                        <p class="mt-2 text-gray-600">
                            Siz bir müşterinizle ilgilenirken veya salon kapalıyken çalan telefonlara cevap
                            veremezsiniz. Müşterileriniz ise 7/24 randevu almak ister.
                        </p>
                    </div>
                    <div class="bg-white p-8 rounded-xl shadow-lg">
                        <div
                            class="bg-yellow-100 text-yellow-600 rounded-full h-12 w-12 flex items-center justify-center"
                        >
                            <Clock class="h-6 w-6" />
                        </div>
                        <h3 class="mt-6 text-xl font-bold text-gray-900">"Unutulan" Randevular (No-Show)</h3>
                        <p class="mt-2 text-gray-600">
                            Müşterinizin gelmediği her randevu, o koltuğun boş kalması ve sizin için gelir kaybı
                            demektir. Tek tek arayıp hatırlatma yapmak ise ekstra zaman kaybıdır.
                        </p>
                    </div>
                    <div class="bg-white p-8 rounded-xl shadow-lg">
                        <div
                            class="bg-purple-100 text-purple-600 rounded-full h-12 w-12 flex items-center justify-center"
                        >
                            <Clapperboard class="h-6 w-6" />
                        </div>
                        <h3 class="mt-6 text-xl font-bold text-gray-900">Karışık Defterler ve WhatsApp</h3>
                        <p class="mt-2 text-gray-600">
                            Hangi personel ne zaman boşta? Hangi müşteri en son ne yaptırmıştı? Defter veya WhatsApp
                            grupları arasında kaybolmak operasyonel kaosa neden olur.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Solution/Benefit Section -->
        <section id="features" class="py-20 sm:py-32 bg-white">
            <div class="container mx-auto px-4">
                <div class="text-center">
                    <h2 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
                        Tek Bir Platformda Tam Kontrol
                    </h2>
                    <p class="mt-4 text-lg text-gray-600">
                        İşinizi kolaylaştıran ve gelirinizi artıran güçlü özellikler.
                    </p>
                </div>
                <div class="mt-16 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-12">
                    <div class="flex gap-6">
                        <div
                            class="bg-blue-100 text-blue-600 rounded-lg h-12 w-12 flex-shrink-0 flex items-center justify-center"
                        >
                            <Zap class="h-6 w-6" />
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">7/24 Online Randevu</h3>
                            <p class="mt-2 text-gray-600">
                                Müşteriniz siz uyurken bile randevu alsın. Size özel online randevu sayfanızı
                                Instagram, Google veya web sitenize ekleyin. Müşterileriniz müsait personeli ve saati
                                seçip randevusunu anında oluştursun.
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-6">
                        <div
                            class="bg-green-100 text-green-600 rounded-lg h-12 w-12 flex-shrink-0 flex items-center justify-center"
                        >
                            <MessageCircle class="h-6 w-6" />
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Otomatik SMS/WhatsApp Hatırlatma</h3>
                            <p class="mt-2 text-gray-600">
                                Gelir kaybına son. Randevudan 24 saat önce ve 2 saat önce müşterilerinize giden
                                otomatik hatırlatma mesajları ile "no-show" (gelmeme) oranını %90'a kadar azaltın.
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-6">
                        <div
                            class="bg-purple-100 text-purple-600 rounded-lg h-12 w-12 flex-shrink-0 flex items-center justify-center"
                        >
                            <Palette class="h-6 w-6" />
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Akıllı Takvim Yönetimi</h3>
                            <p class="mt-2 text-gray-600">
                                Tüm personelinizi tek ekrandan yönetin. Kimin randevusu var, kim molada, kim boşta
                                anında görün. Sürükle-bırak ile randevuları kolayca yeniden planlayın.
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-6">
                        <div
                            class="bg-yellow-100 text-yellow-600 rounded-lg h-12 w-12 flex-shrink-0 flex items-center justify-center"
                        >
                            <Contact class="h-6 w-6" />
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Müşteri Kayıtları (CRM)</h3>
                            <p class="mt-2 text-gray-600">
                                Müşterinizi tanıyın. Müşterilerinizin tüm geçmiş işlemlerini, iletişim bilgilerini ve
                                onlara özel notları (örn: "saç boya formülü", "kahve sever") tek yerde güvenle saklayın.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- How it Works Section -->
        <section class="py-20 sm:py-32">
            <div class="container mx-auto px-4">
                <div class="text-center">
                    <h2 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
                        Başlamak Sadece 5 Dakika
                    </h2>
                    <p class="mt-4 text-lg text-gray-600">3 basit adımda salonunuzu dijitalleştirin.</p>
                </div>
                <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                    <div class="bg-white p-8 rounded-xl shadow-lg flex flex-col items-center">
                        <div
                            class="bg-gray-200 text-gray-700 rounded-full h-16 w-16 flex items-center justify-center text-2xl font-bold"
                        >
                            1
                        </div>
                        <h3 class="mt-6 text-xl font-bold text-gray-900">Salonunuzu Kaydedin</h3>
                        <p class="mt-2 text-gray-600">
                            Temel salon bilgilerinizi girerek hesabınızı anında oluşturun.
                        </p>
                    </div>
                    <div class="bg-white p-8 rounded-xl shadow-lg flex flex-col items-center">
                        <div
                            class="bg-gray-200 text-gray-700 rounded-full h-16 w-16 flex items-center justify-center text-2xl font-bold"
                        >
                            2
                        </div>
                        <h3 class="mt-6 text-xl font-bold text-gray-900">Personel ve Hizmetleri Ekleyin</h3>
                        <p class="mt-2 text-gray-600">
                            Personellerinizi, çalışma saatlerini ve sunduğunuz hizmetleri (fiyat ve süreleriyle)
                            sisteme bir kez tanımlayın.
                        </p>
                    </div>
                    <div class="bg-white p-8 rounded-xl shadow-lg flex flex-col items-center">
                        <div
                            class="bg-blue-600 text-white rounded-full h-16 w-16 flex items-center justify-center text-2xl font-bold"
                        >
                            3
                        </div>
                        <h3 class="mt-6 text-xl font-bold text-gray-900">Randevu Almaya Başlayın!</h3>
                        <p class="mt-2 text-gray-600">
                            Online randevu linkinizi müşterilerinizle paylaşın. Gelen randevular otomatik olarak
                            takviminize düşsün.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section id="testimonials" class="py-20 sm:py-32 bg-white">
            <div class="container mx-auto px-4">
                <div class="text-center">
                    <h2 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
                        Bize Güvenen Salon Sahipleri Ne Diyor?
                    </h2>
                    <p class="mt-4 text-lg text-gray-600">Gerçek kullanıcılarımızdan gerçek yorumlar.</p>
                </div>
                <div class="mt-16 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="bg-gray-50 p-8 rounded-xl border border-gray-200">
                        <div class="flex items-center gap-4">
                            <img
                                class="h-14 w-14 rounded-full object-cover"
                                src="https://randomuser.me/api/portraits/women/68.jpg"
                                alt="Ayşe K."
                            />
                            <div>
                                <p class="font-bold text-gray-900">Ayşe K.</p>
                                <p class="text-sm text-gray-600">Salon Sahibi, İstanbul</p>
                            </div>
                        </div>
                        <p class="mt-6 text-gray-700 italic">
                            "e-makas sayesinde gelmeyen müşteri derdimiz bitti. Özellikle SMS hatırlatmaları
                            hayat kurtardı. Artık telefon trafiği olmadan işime odaklanabiliyorum."
                        </p>
                    </div>
                    <div class="bg-gray-50 p-8 rounded-xl border border-gray-200">
                        <div class="flex items-center gap-4">
                            <img
                                class="h-14 w-14 rounded-full object-cover"
                                src="https://randomuser.me/api/portraits/men/32.jpg"
                                alt="Mehmet T."
                            />
                            <div>
                                <p class="font-bold text-gray-900">Mehmet T.</p>
                                <p class="text-sm text-gray-600">Erkek Kuaförü, Ankara</p>
                            </div>
                        </div>
                        <p class="mt-6 text-gray-700 italic">
                            "Eskiden defter tutardık, tam bir kaostu. Şimdi hangi personel ne kadar ciro yapmış, ay
                            sonunda ne kadar prim alacak, hepsini tek tuşla görüyorum. Keşke daha önce geçseydim."
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section id="pricing" class="py-20 sm:py-32">
            <div class="container mx-auto px-4">
                <div class="text-center">
                    <h2 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
                        Her Salona Uygun Bir Plan Var
                    </h2>
                    <p class="mt-4 text-lg text-gray-600">
                        İşletmenizin ihtiyaçlarına en uygun paketi seçin ve dijitalleşmeye bugün başlayın.
                    </p>
                </div>

                <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Free Plan -->
                    <div class="bg-white rounded-2xl shadow-lg p-8 flex flex-col">
                        <h3 class="text-2xl font-bold text-gray-900">Başlangıç</h3>
                        <p class="mt-2 text-gray-600">Tek çalışan veya yeni başlayanlar için.</p>
                        <p class="mt-8">
                            <span class="text-5xl font-extrabold text-gray-900">0 TL</span>
                            <span class="text-lg font-medium text-gray-500">/ ay</span>
                        </p>
                        <Button variant="outline" size="lg" class="w-full mt-8 text-lg">Ücretsiz Başla</Button>
                        <ul class="mt-8 space-y-4 text-gray-600 flex-grow">
                            <li class="flex items-center gap-3">
                                <Check class="h-5 w-5 text-blue-600" />
                                <span>1 Personel</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <Check class="h-5 w-5 text-blue-600" />
                                <span>50 Randevu / ay</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <Check class="h-5 w-5 text-blue-600" />
                                <span>Online Randevu Sayfası</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <Check class="h-5 w-5 text-blue-600" />
                                <span>Müşteri Kayıtları</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Pro Plan (Recommended) -->
                    <div class="bg-blue-600 text-white rounded-2xl shadow-2xl p-8 relative flex flex-col">
                        <div class="absolute top-0 -translate-y-1/2 w-full left-0 px-4">
                            <div class="bg-gray-900 text-white text-sm font-semibold text-center rounded-full px-3 py-1 w-fit mx-auto">
                                En Popüler
                            </div>
                        </div>
                        <h3 class="text-2xl font-bold">Profesyonel</h3>
                        <p class="mt-2 text-blue-200">Büyüyen salonlar ve ekipler için ideal.</p>
                        <p class="mt-8">
                            <span class="text-5xl font-extrabold">249 TL</span>
                            <span class="text-lg font-medium text-blue-200">/ ay</span>
                        </p>
                        <Button size="lg" class="w-full mt-8 text-lg bg-white text-blue-600 hover:bg-blue-50">14 Gün Ücretsiz Dene</Button>
                        <ul class="mt-8 space-y-4 flex-grow">
                            <li class="flex items-center gap-3">
                                <Check class="h-5 w-5" />
                                <span>5 Personel</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <Check class="h-5 w-5" />
                                <span>Sınırsız Randevu</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <Check class="h-5 w-5" />
                                <span>Otomatik SMS Hatırlatmaları</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <Check class="h-5 w-5" />
                                <span>Raporlama ve Analitik</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <Check class="h-5 w-5" />
                                <span>7/24 Destek</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Business Plan -->
                    <div class="bg-white rounded-2xl shadow-lg p-8 flex flex-col">
                        <h3 class="text-2xl font-bold text-gray-900">İşletme</h3>
                        <p class="mt-2 text-gray-600">Büyük salonlar ve zincirler için.</p>
                        <p class="mt-8">
                            <span class="text-5xl font-extrabold text-gray-900">499 TL</span>
                            <span class="text-lg font-medium text-gray-500">/ ay</span>
                        </p>
                        <Button variant="outline" size="lg" class="w-full mt-8 text-lg">Bize Ulaşın</Button>
                        <ul class="mt-8 space-y-4 text-gray-600 flex-grow">
                            <li class="flex items-center gap-3">
                                <Check class="h-5 w-5 text-blue-600" />
                                <span>Sınırsız Personel</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <Check class="h-5 w-5 text-blue-600" />
                                <span>Tüm Profesyonel Özellikleri</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <Check class="h-5 w-5 text-blue-600" />
                                <span>Gelişmiş Raporlama</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <Check class="h-5 w-5 text-blue-600" />
                                <span>Öncelikli Destek</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <Check class="h-5 w-5 text-blue-600" />
                                <span>API Erişimi</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="py-20 sm:py-32 bg-white">
            <div class="container mx-auto px-4 max-w-4xl">
                <div class="text-center">
                    <h2 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
                        Sıkça Sorulan Sorular
                    </h2>
                    <p class="mt-4 text-lg text-gray-600">Aklınıza takılan başka bir soru mu var? Bize ulaşın.</p>
                </div>
                <div class="mt-12 space-y-4">
                    <div
                        v-for="(faq, index) in faqs"
                        :key="index"
                        class="bg-gray-50 border border-gray-200 rounded-lg"
                    >
                        <button
                            class="w-full flex justify-between items-center p-6 text-left"
                            @click="faq.open = !faq.open"
                        >
                            <span class="text-lg font-medium text-gray-900">{{ faq.question }}</span>
                            <ChevronRight
                                class="h-6 w-6 text-gray-500 transition-transform"
                                :class="{ 'rotate-90': faq.open }"
                            />
                        </button>
                        <div v-if="faq.open" class="px-6 pb-6 text-gray-600">
                            {{ faq.answer }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Final CTA Section -->
        <section class="py-20 sm:py-32">
            <div class="container mx-auto px-4 text-center">
                <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900">
                    Salonunuzu Dijitale Taşımaya Hazır Mısınız?
                </h2>
                <p class="text-lg md:text-xl text-gray-600 max-w-3xl mx-auto mt-6">
                    Defteri, kalemi ve kaçan telefonları unutun. 14 gün boyunca hiçbir ücret ödemeden ve kredi kartı
                    bilgilerinizi girmeden <span class="font-semibold text-blue-600">e-makas</span>'ı deneyin.
                </p>
                <div class="mt-8">
                    <Button size="lg" class="text-lg">Ücretsiz Hesabınızı Şimdi Oluşturun</Button>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white">
            <div class="container mx-auto px-4 py-12">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                    <div>
                        <h3 class="font-bold text-lg">e-makas</h3>
                        <p class="mt-2 text-gray-400">Salon yönetimini kolaylaştırın.</p>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-300">Ürün</h3>
                        <ul class="mt-4 space-y-2">
                            <li><a href="#features" class="hover:text-blue-400">Özellikler</a></li>
                            <li><a href="#pricing" class="hover:text-blue-400">Fiyatlandırma</a></li>
                            <li><a href="#" class="hover:text-blue-400">Giriş Yap</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-300">Kaynaklar</h3>
                        <ul class="mt-4 space-y-2">
                            <li><a href="#" class="hover:text-blue-400">Blog</a></li>
                            <li><a href="#" class="hover:text-blue-400">Yardım Merkezi</a></li>
                            <li><a href="#" class="hover:text-blue-400">Bize Ulaşın</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-300">Yasal</h3>
                        <ul class="mt-4 space-y-2">
                            <li><a href="#" class="hover:text-blue-400">Gizlilik Politikası</a></li>
                            <li><a href="#" class="hover:text-blue-400">Hizmet Şartları</a></li>
                        </ul>
                    </div>
                </div>
                <div class="mt-12 border-t border-gray-800 pt-8 text-center text-gray-500">
                    &copy; {{ new Date().getFullYear() }} e-makas. Tüm hakları saklıdır.
                </div>
            </div>
        </footer>
    </div>
</template>
<style>
html {
    scroll-behavior: smooth;
    scroll-padding-top: 5rem; /* Adjust this value based on your header's height */
}
</style>
