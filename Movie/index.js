// ==========================
// Movie Card Carousel
// ==========================
const card = document.querySelector(".movie-card");
const titleEl = document.querySelector(".movie-card-info h2");
const ratingEl = document.querySelector(".rating span");
const descEl = document.querySelector(".description");
const imageEl = document.querySelector(".movie-image");

const spotlightMovies = JSON.parse(card.dataset.movies);
let currentIndex = 0;

function updateCard(movie) {
    card.style.opacity = 0;

    setTimeout(() => {
        titleEl.textContent = movie.title;
        ratingEl.textContent = movie.vote_count + " voters";
        descEl.textContent = movie.overview;
        imageEl.style.backgroundImage = `url('https://image.tmdb.org/t/p/w500${movie.poster_path}')`;
        card.style.opacity = 1;
    }, 500);
}

// Show first movie on page load
updateCard(spotlightMovies[currentIndex]);

// Auto-cycle movies every 4 seconds
setInterval(() => {
    currentIndex = (currentIndex + 1) % spotlightMovies.length;
    updateCard(spotlightMovies[currentIndex]);
}, 4000);

// ==========================
// Video Modal
// ==========================
const videoModal = document.getElementById("videoModal");
const trailerVideo = document.getElementById("trailerVideo");
const videoSource = document.getElementById("videoSource");
const trailerIframe = document.getElementById("trailerIframe");
const closeModal = document.getElementById("closeModal");

function closeVideoModal() {
    videoModal.classList.remove("active");
    trailerVideo.pause();
    trailerVideo.src = "";
    trailerIframe.src = "";
}

// Play trailer on click
document.querySelectorAll(".play-btn, .trailer-text").forEach(btn => {
    btn.addEventListener("click", (e) => {
        e.stopPropagation();
        let videoUrl = "";

        if (btn.classList.contains("trailer-text")) {
            videoUrl = spotlightMovies[currentIndex].trailer;
        } else {
            videoUrl = btn.getAttribute("data-video");
        }

        if (!videoUrl) return alert("Trailer not available");

        videoModal.classList.add("active");

        if (videoUrl.includes("youtube.com") || videoUrl.includes("youtu.be")) {
            trailerVideo.style.display = "none";
            trailerVideo.pause();
            trailerIframe.style.display = "block";

            let embedUrl = videoUrl;
            if (embedUrl.includes("watch?v=")) embedUrl = embedUrl.replace("watch?v=", "embed/");
            if (embedUrl.includes("youtu.be/")) embedUrl = embedUrl.replace("youtu.be/", "youtube.com/embed/");
            embedUrl += embedUrl.includes("?") ? "&autoplay=1" : "?autoplay=1";

            trailerIframe.src = embedUrl;
        } else {
            trailerIframe.style.display = "none";
            trailerIframe.src = "";
            trailerVideo.style.display = "block";
            videoSource.src = videoUrl;
            trailerVideo.load();
            trailerVideo.play();
        }
    });
});

// Close modal events
closeModal.addEventListener("click", closeVideoModal);
videoModal.addEventListener("click", e => {
    if (e.target === videoModal) closeVideoModal();
});

// ==========================
// Profile Dropdown
// ==========================
const profilePic = document.querySelector('.profile-pic');
const profileDropdown = document.querySelector('.profile-dropdown');

if (profilePic) {
    profilePic.addEventListener('click', (e) => {
        e.stopPropagation();
        profileDropdown.classList.toggle('active');
    });

    window.addEventListener('click', (e) => {
        if (!profileDropdown.contains(e.target) && !profilePic.contains(e.target)) {
            profileDropdown.classList.remove('active');
        }
    });
}
