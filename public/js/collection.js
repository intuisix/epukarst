function createCollectionResponders(blockSelector, additionSelector, deletionSelector, deletionMessage = null) {

    /* Obtenir le nombre initial de mesures */
    var itemCount = +$(blockSelector).length;

    /* Définir l'action des boutons de suppression */
    function setupDeleteButtons() {
        /* Eviter plusieurs demandes de confirmation sur un même événement */
        $(deletionSelector).off("click");
        /* Supprimer le bloc de formulaire en cas de clic */
        $(deletionSelector).on("click", function() {
            if ((null == deletionMessage) || confirm(deletionMessage)) {
                $(this.dataset.target).remove();
            }
        });
    }

    /* Définir l'action des boutons d'ajout */
    function setupAddButtons() {
        setupDeleteButtons();
        /* Ajouter un bloc de formulaire en cas de clic */
        $(additionSelector).click(function() {
            itemCount++;
            const prototype = $(blockSelector).data('prototype');
            $(blockSelector).append(prototype.replace(/__name__/g, itemCount));
            setupDeleteButtons();
        });
    }

    return setupAddButtons;
}
