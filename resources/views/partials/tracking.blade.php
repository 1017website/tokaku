{{-- Script tracking/ads. Letakkan setelah tag <head> dibuka (GTM/GA) & sebelum </body> jika perlu. --}}
@php
    $gaId    = $appSettings['google_analytics_id'] ?? null;
    $adsId   = $appSettings['google_ads_id'] ?? null;
    $gtmId   = $appSettings['gtm_id'] ?? null;
    $pixelId = $appSettings['meta_pixel_id'] ?? null;
    $gtagId  = $gaId ?: $adsId; // muat gtag.js sekali, pakai id pertama yang ada
@endphp

{{-- Google Tag Manager --}}
@if($gtmId)
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{{ $gtmId }}');</script>
@endif

{{-- Google Analytics (GA4) + Google Ads via gtag.js --}}
@if($gtagId)
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $gtagId }}"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    @if($gaId) gtag('config', '{{ $gaId }}'); @endif
    @if($adsId) gtag('config', '{{ $adsId }}'); @endif
</script>
@endif

{{-- Meta (Facebook) Pixel --}}
@if($pixelId)
<script>
    !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
    n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
    document,'script','https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '{{ $pixelId }}');
    fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id={{ $pixelId }}&ev=PageView&noscript=1"/></noscript>
@endif
