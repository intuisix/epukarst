function createCollectionResponders(counterSelector, blockSelector, additionSelector, deletionSelector, deletionMessage) {

    /* Définir l'action des boutons de suppression */
    function setupDeleteButtons() {
        /* Pour éviter plusieurs demandes de confirmation sur un même événement */
        $(deletionSelector).off("click");
        /* Supprimer la division en cas de clic */
        $(deletionSelector).on("click", function() {
            if (confirm(deletionMessage)) {
                $(this.dataset.target).remove();
            }
        });
    }

    /* Définir l'action des boutons d'ajout */
    function setupAddButtons() {
        setupDeleteButtons();
        /* Obtenir le nombre initial de mesures */
        $(counterSelector).val(+$(blockSelector + ' div.form-group').length);
        /* Ajouter une division en cas de clic */
        $(additionSelector).click(function() {
            const index = +$(counterSelector).val();
            const prototype = $(blockSelector).data('prototype');
            $(blockSelector).append(prototype.replace(/__name__/g, index));
            $(counterSelector).val(index + 1);
            setupDeleteButtons();
        });
    }

    return setupAddButtons;
}
