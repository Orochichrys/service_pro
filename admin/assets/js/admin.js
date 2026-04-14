/**
 * Fonctions JavaScript pour le Panel Administration
 */

// --- Gestion des Catégories & Services ---
function remplirModalEdit(btn) {
    const editId = document.getElementById('edit_id');
    const editNom = document.getElementById('edit_nom');
    const editIcone = document.getElementById('edit_icone');
    
    if(editId) editId.value = btn.getAttribute('data-id');
    if(editNom) editNom.value = btn.getAttribute('data-nom');
    if(editIcone) editIcone.value = btn.getAttribute('data-icone');
}

function remplirModalService(btn) {
    const editSerId = document.getElementById('edit_ser_id');
    const editSerNom = document.getElementById('edit_ser_nom');
    const editSerCat = document.getElementById('edit_ser_cat');
    
    if(editSerId) editSerId.value = btn.getAttribute('data-id');
    if(editSerNom) editSerNom.value = btn.getAttribute('data-nom');
    if(editSerCat) editSerCat.value = btn.getAttribute('data-cat');
}

// --- Gestion des Localisations ---
function remplirRegion(btn) {
    const regId = document.getElementById('edit_reg_id');
    const regNom = document.getElementById('edit_reg_nom');
    
    if(regId) regId.value = btn.getAttribute('data-id');
    if(regNom) regNom.value = btn.getAttribute('data-nom');
}

function remplirDept(btn) {
    const deptId = document.getElementById('edit_dept_id');
    const deptNom = document.getElementById('edit_dept_nom');
    const deptReg = document.getElementById('edit_dept_reg');
    
    if(deptId) deptId.value = btn.getAttribute('data-id');
    if(deptNom) deptNom.value = btn.getAttribute('data-nom');
    if(deptReg) deptReg.value = btn.getAttribute('data-reg');
}

function remplirVille(btn) {
    const villeId = document.getElementById('edit_ville_id');
    const villeNom = document.getElementById('edit_ville_nom');
    const villeDept = document.getElementById('edit_ville_dept');
    
    if(villeId) villeId.value = btn.getAttribute('data-id');
    if(villeNom) villeNom.value = btn.getAttribute('data-nom');
    if(villeDept) villeDept.value = btn.getAttribute('data-dept');
}

function remplirQuartier(btn) {
    const qId = document.getElementById('edit_q_id');
    const qNom = document.getElementById('edit_q_nom');
    const qVille = document.getElementById('edit_q_ville');
    
    if(qId) qId.value = btn.getAttribute('data-id');
    if(qNom) qNom.value = btn.getAttribute('data-nom');
    if(qVille) qVille.value = btn.getAttribute('data-ville');
}
