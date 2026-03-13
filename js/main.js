// handles all the interactive bits. nav highlighting, image gallery, filter toggle, and upload previews

document.addEventListener("DOMContentLoaded", function () {

    highlightCurrentPage();   // mark the current nav link as active
    setupImageGallery();      // swap main image when a thumbnail is clicked (car detail page)
    setupFilterToggle();      // show/hide filter sidebar on mobile (listings page)
    showUploadPreviews();     // show image previews when files are picked (post-listing page)

});


// marks the current nav link as active based on the page url
function highlightCurrentPage() {
    const navLinks = document.querySelectorAll("nav a.nav-link");

    navLinks.forEach(function (link) {
        if (link.href === location.href) {
            link.classList.add("active");
            link.setAttribute("aria-current", "page"); // tells screen readers which page the user is on.
        }
    });
}


// swaps the main car photo when a thumbnail is clicked
function setupImageGallery() {
    const mainImage = document.getElementById("mainCarImage");
    const thumbs    = document.querySelectorAll(".gallery-thumb");

    // no gallery on this page, nothing to do
    if (!mainImage || thumbs.length === 0) return;

    thumbs.forEach(function (thumb) {
        thumb.addEventListener("click", function () {

            // swap the main image to the one that was clicked
            mainImage.src = this.src;
            mainImage.alt = this.alt;

            // move the active highlight to the clicked thumbnail
            thumbs.forEach(function (t) { t.classList.remove("active"); });
            this.classList.add("active");

        });
    });

    // highlight the first thumbnail when the page loads
    thumbs[0].classList.add("active");
}


// shows small previews of the selected photos before the form is submitted
function showUploadPreviews() {
    const fileInput        = document.getElementById("carImages");
    const previewContainer = document.getElementById("imagePreview");

    // not on the post-listing page, nothing to do
    if (!fileInput || !previewContainer) return;

    fileInput.addEventListener("change", function () {
        previewContainer.innerHTML = ""; // clear out any previous previews.

        const files = Array.from(this.files);

        if (files.length > 5) {
            previewContainer.innerHTML =
                '<p class="text-danger small">Maximum 5 images allowed.</p>';
            return;
        }

        files.forEach(function (file, index) {

            // skip non-image files
            if (!file.type.startsWith("image/")) return;

            const reader = new FileReader(); // reads the file locally. no upload happens here.

            reader.onload = function (e) {
                const wrapper = document.createElement("div");
                wrapper.className = "preview-thumb-wrap";

                const img = document.createElement("img");
                img.src       = e.target.result; // data url from FileReader, used directly as the img src.
                img.alt       = file.name;
                img.className = "preview-thumb-img";

                // first image gets a cover badge since it will be used as the listing cover photo
                if (index === 0) {
                    const badge = document.createElement("span");
                    badge.className   = "preview-cover-badge";
                    badge.textContent = "Cover";
                    wrapper.appendChild(badge);
                }

                wrapper.appendChild(img);
                previewContainer.appendChild(wrapper);
            };

            reader.readAsDataURL(file);
        });
    });
}


// toggles the filter sidebar open or closed when the button is tapped on mobile
function setupFilterToggle() {
    const toggleBtn     = document.getElementById("filterToggleBtn");
    const filterSidebar = document.getElementById("filterSidebar");

    // not on the listings page, nothing to do
    if (!toggleBtn || !filterSidebar) return;

    toggleBtn.addEventListener("click", function () {
        const isHidden = filterSidebar.classList.toggle("collapsed");

        // update the button label to match the current state
        this.textContent = isHidden ? "Show Filters" : "Hide Filters";

        // update aria-expanded so screen readers know if the panel is open or closed
        this.setAttribute("aria-expanded", isHidden ? "false" : "true");
    });
}
