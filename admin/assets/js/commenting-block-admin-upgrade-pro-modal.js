//Upgrage pro modal popup js
document.addEventListener('DOMContentLoaded', function () {
    const premiumStars = document.querySelectorAll('.cf_premium_star');

    if (premiumStars) {
        const modal = document.getElementById('cf-plugin_upgrademodal');
        const modalCloseBtn = document.querySelector('.modal-close-btn');
        premiumStars.forEach(function(premiumStar) {
            premiumStar.addEventListener('click', function (event) {
                event.preventDefault(); // Prevent the default anchor behavior
                modal.classList.add('cf-active-modal');
                document.querySelector('body').style.overflowY = "hidden";
            });
        });
    
        modalCloseBtn.addEventListener('click', function () { 
            modal.classList.remove('cf-active-modal');
            document.querySelector('body').style.overflowY = "unset";
        });
    }

});