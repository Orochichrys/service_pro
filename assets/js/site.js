/**
 * Fonctions JavaScript globales pour le site ServicePro
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // --- Catalogue : Slider de prix ---
    const range = document.getElementById('priceRange');
    const value = document.getElementById('priceValue');
    if (range && value) {
        range.addEventListener('input', () => {
            value.textContent = new Intl.NumberFormat().format(range.value);
        });
    }

    // --- Mes Commandes : Modal d'avis & Étoiles ---
    const modalAvis = document.getElementById('modalAvis');
    if (modalAvis) {
        modalAvis.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const idInput = document.getElementById('id_commande_modal');
            const nameSpan = document.getElementById('service_nom_modal');
            
            if(idInput) idInput.value = button.getAttribute('data-id');
            if(nameSpan) nameSpan.textContent = button.getAttribute('data-service');
        });

        const stars = document.querySelectorAll('.rating-star');
        const noteInput = document.getElementById('note_input');
        if (stars.length > 0 && noteInput) {
            stars.forEach(star => {
                star.addEventListener('click', function() {
                    const val = this.getAttribute('data-value');
                    noteInput.value = val;
                    
                    stars.forEach(s => {
                        if(s.getAttribute('data-value') <= val) {
                            s.classList.replace('bi-star', 'bi-star-fill');
                        } else {
                            s.classList.replace('bi-star-fill', 'bi-star');
                        }
                    });
                });
                
                // État par défaut (5 étoiles au début si non cliqué)
                if(star.getAttribute('data-value') <= noteInput.value) {
                    star.classList.replace('bi-star', 'bi-star-fill');
                }
            });
        }
    }
});
