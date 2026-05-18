<div id="collab-popup-overlay" class="dossier-popup-hidden">
    <div class="collab-popup-card">
        <div class="popup-red-tape">OFFICIAL COLLABORATION</div>
        <div class="popup-header">
            <span class="popup-tag">// PARTNERSHIP INFORMATION</span>
            <h2 class="popup-title">KOLABORASI AKADEMIK.</h2>
            <div class="popup-divider"></div>
        </div>
        
        <div class="popup-logos-wrapper">
            
            <div class="logo-box">
                <img src="assets/images/gambar-mentahan/Logo_Polije1.png" 
                     alt="Logo POLIJE" 
                     class="collab-logo" 
                     onerror="this.src='https://placehold.co/100x100/0A1D37/FFF?text=POLIJE'">
            </div>
            
            <span class="collab-x">
                <svg width="50" height="50" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M23.5 20.5C38 37.5 61 68 76.5 83.5C73.5 86 69.5 86.5 66 84C51 68.5 28 37 14.5 22.5C16.5 19.5 20.5 18.5 23.5 20.5Z" fill="#D63D3D"/>
                    <path d="M78.5 19.5C64 36.5 39 66 23.5 82.5C26.5 85.5 30.5 85 34 82.5C49 67 74 38 88.5 23.5C86.5 20.5 82.5 18.5 78.5 19.5Z" fill="#D63D3D"/>
                    <circle cx="85" cy="80" r="3.5" fill="#D63D3D"/>
                    <circle cx="75" cy="89" r="2" fill="#D63D3D"/>
                    <circle cx="20" cy="85" r="3" fill="#D63D3D"/>
                    <circle cx="26" cy="14" r="2.5" fill="#D63D3D"/>
                    <circle cx="92" cy="28" r="4" fill="#D63D3D"/>
                    <circle cx="88" cy="18" r="2" fill="#D63D3D"/>
                    <circle cx="12" cy="30" r="2" fill="#D63D3D"/>
                </svg>
            </span>
            
            <div class="logo-box">
                <img src="assets/images/gambar-mentahan/logo.png" 
                     alt="Logo Woelandari" 
                     class="collab-logo" 
                     onerror="this.src='https://placehold.co/100x125/D63D3D/FFF?text=CAFE'">
            </div>
        </div>
        <div class="popup-body">
            <p class="typewriter-text">
                Selamat datang di sistem arsip digital <strong>Woelandari Coffee Lab</strong>.
            </p>
            <p class="typewriter-text">
                Website ini merupakan manifestasi dari proyek kolaborasi akademik antara <strong>Politeknik Negeri Jember (POLIJE)</strong> dan <strong>Woelandari Coffee Lab</strong> Dirancang untuk mengintegrasikan teknologi informasi dengan kultur seduh kopi Nusantara.
            </p>
            <div class="popup-auth">
            <span>Developed in Collaboration with POLIJE</span>
            <span>System Status: Active</span>
            </div>
        </div>

        <button id="btn-acknowledge" class="btn-popup-execute">
           EXPLORE PLATFORM <i class="fa-solid fa-check"></i>
        </button>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const popupOverlay = document.getElementById("collab-popup-overlay");
    const btnAcknowledge = document.getElementById("btn-acknowledge");

    if (popupOverlay && btnAcknowledge) {
        if (!localStorage.getItem("woelandari_collab_seen")) {
            setTimeout(() => {
                popupOverlay.classList.remove("dossier-popup-hidden");
            }, 500);
        }

        btnAcknowledge.addEventListener("click", function() {
            popupOverlay.classList.add("dossier-popup-hidden");
            localStorage.setItem("woelandari_collab_seen", "true");
        });
    }
});
</script>