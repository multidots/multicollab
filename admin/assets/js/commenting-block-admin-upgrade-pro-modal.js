//Upgrage pro modal popup js
document.addEventListener('DOMContentLoaded', function () {
    const premiumStars = document.querySelectorAll('.cf_premium_star, .cf-upgrade-btn');
    const modal = document.getElementById('cf-plugin_upgrademodal');

    if (premiumStars.length && modal) {
        const modalCloseBtn = modal.querySelector('.modal-close-btn');

        premiumStars.forEach(function (premiumStar) {
            premiumStar.addEventListener('click', function (event) {
                event.preventDefault();
                modal.classList.add('cf-active-modal');
                document.body.style.overflowY = "hidden";
            });
        });

        if (modalCloseBtn) {
            modalCloseBtn.addEventListener('click', function () {
                modal.classList.remove('cf-active-modal');
                document.body.style.overflowY = "unset";
            });
        }
    }
});
