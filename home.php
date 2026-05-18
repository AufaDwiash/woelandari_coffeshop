
<section id="home">
<div class="interactive-scene" id="scene" onclick="document.getElementById('about').scrollIntoView({behavior: 'smooth'})">
    
    <img src="assets/images/background/layer1.png" class="layer layer-bg">

    <div class="character-group" id="characterGroup">
        <div id="bubble" class="lab-note">
            <span id="displayText">Halo! Ada riset kopi apa hari ini?</span>
        </div>
        <img src="assets/images/background/depan.png" class="layer-orang" id="charImg">
    </div>

    <img src="assets/images/background/layer2.png" class="layer layer-pagar">
</div>

<script>
    // Bungkus script dengan fungsi agar tidak bentrok dengan script halaman lain
    (function() {
        const scene = document.getElementById('scene');
        const charGroup = document.getElementById('characterGroup');
        const charImg = document.getElementById('charImg');
        const bubble = document.getElementById('bubble');
        const displayText = document.getElementById('displayText');

        const assets = {
            depan: 'assets/images/background/depan.png',
            kiri: 'assets/images/background/kiri.png',
            kanan: 'assets/images/background/kanan.png'
        };

        Object.values(assets).forEach(src => {
            const img = new Image();
            img.src = src;
        });

        const messages = [
            "Halo! Mari kita mulai eksperimen hari ini.",
            "Ingin mencoba racikan beans terbaru?",
            "Siap menemukan rasa yang presisi?",
            "Senang melihatmu kembali di lab kami!",
            "Klik untuk melihat inventaris menu kami."
        ];

        let currentPose = "depan"; 
        let idleTimer; 

        function getRandomMsg() {
            return messages[Math.floor(Math.random() * messages.length)];
        }

        scene.addEventListener('mousemove', (e) => {
            charGroup.classList.remove('smooth-reset');
            const x = e.clientX;
            const width = window.innerWidth;
            
            const moveX = (x / width - 0.5) * 85; 
            charGroup.style.transform = `translateX(calc(-50% + ${moveX}vw))`;

            let newPose;
            if (x < width * 0.48) { newPose = "kiri"; } 
            else if (x > width * 0.52) { newPose = "kanan"; } 
            else { newPose = "depan"; }

            if (newPose !== currentPose) {
                currentPose = newPose;
                charImg.src = assets[currentPose];

                if (currentPose === "depan") {
                    displayText.innerText = getRandomMsg();
                    bubble.classList.add('show');
                } else {
                    bubble.classList.remove('show');
                }
            }

            clearTimeout(idleTimer);
            idleTimer = setTimeout(() => {
                if (currentPose !== "depan") {
                    currentPose = "depan";
                    charImg.src = assets.depan;
                    displayText.innerText = getRandomMsg();
                    bubble.classList.add('show');
                }
            }, 150);
        });

        scene.addEventListener('mouseleave', () => {
            clearTimeout(idleTimer);
            charGroup.classList.add('smooth-reset');
            charGroup.style.transform = `translateX(-50%)`;
            charImg.src = assets.depan;
            bubble.classList.remove('show');
            currentPose = "depan";
        });
    })();
</script>
</section>

