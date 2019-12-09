/* Gère les réponses aux utilisateurs sur les boutons d'ajout et de suppression
des éléments d'une CollectionType de Symfony.

@param $blockSelector est l'identifiant de la division contenant les sous-
formulaires correspondant aux éléments de la collection. Ne pas oublier d'y
préfixer '#'. Ce paramètre doit référencer un seul élément de document.

@param $additionSelector est l'identifiant du ou des boutons d'ajout d'élément,
qui déclencheront l'insertion de sous-formulaires accueillant les nouveaux
éléments. Il doit désigner des boutons, qui doivent se trouver n'importe où
dans le document, éventuellement même à l'intérieur de sous-formulaires. Chaque
sous-formulaire sera créé avec un identifiant comme si Symfony le générait
lui-même (id="{{id}}" en Twig). Ce paramètre peut être nul pour ne pas gérer
l'ajout.

@param delectionSelector est l'identifiant du ou des boutons de suppression
d'élément, qui déclencheront la suppression des sous-formulaire en vue de
supprimer l'élément qui leur correspond. Il doit désigner des boutons dont la
cible désigne le sous-formulaire à supprimer (data-target="#{{id}}"" en Twig).
Ce paramètre peut être nul pour ne pas gérer la suppression.

@param $deletionMessage est le message de la confirmation qui sera demandée à
l'utilisateur lorsqu'il clique sur le bouton de suppression. Ce paramètre peut
être nul pour procéder à la suppression sans demander de confirmation.

@param $copyValues est un tableau associatif permettant de préremplir les
nouveaux sous-formulaires avec des valeurs par défaut provenant d'autres
éléments (par exemple, issues du formulaire principal).
*/
function createCollectionResponders($blockSelector, $additionSelector, $deletionSelector, $deletionMessage = null, $copyValues = null) {

    /* Symfony a déjà numéroté de façon unique les éléments déjà présents;
    obtenir le nombre initial d'éléments dans la collection, afin de pouvoir
    continuer la numérotation en réponse aux ajouts d'éléments demandés par
    l'utilisateur */
    var $itemIndex = +$($blockSelector).children().length;

    /* Définit l'action des boutons de suppression d'élément, qui sont inclus
    dans un ou plusiers sous-formulaire(s) */
    function setupDeleteButtons($selector)
    {
        var $buttons = $($selector);
        if (null != $buttons) {
            /* Supprimer le sous-formulaire en cas de clic, après avoir demandé
            confirmation à l'utilisateur si un message a été spécifié */
            $buttons.click(function() {
                if ((null == $deletionMessage) || confirm($deletionMessage)) {
                    $(this.dataset.target).remove();
                }
            });
        }
    }

    /* Retourne le sélecteur d'un sous-formulaire */
    function getItemSelector($index)
    {
        return $blockSelector + '_' + $index;
    }

    /* Définit l'action des boutons d'ajout d'élément, qui sont inclus dans le
    formulaire principal et/ou dans un ou plusieurs sous-formulaires */
    function setupAddButtons($selector) 
    {
        $buttons = $($selector);
        if (null != $buttons) {
            /* Ajouter un sous-formulaire en cas de clic */
            $buttons.click(function() {
                /* Attribuer un numéro unique à l'élément, en principe dans la
                continuité des numéros existants; cependant, lorsque le formulaire
                est affiché une deuxième ou une énième fois après que l'utilisateur
                ait supprimé des éléments, il se peut que des discontinuités soient
                présentes dans la numérotation, ou même des inversions; par
                conséquent, la boucle permet de trouver un numéro qui n'est pas
                encore attribué */
                var $itemSelector = getItemSelector($itemIndex);
                while ($($itemSelector).length) {
                    $itemIndex++;
                    $itemSelector = getItemSelector($itemIndex);
                }
                /* Un prototype du sous-formulaire a été créé par Symfony: pour
                ajouter le sous-formulaire correspondant au nouvel élément, il
                suffit le dupliquer et d'y remplacer la chaîne __name__ par
                l'indice du nouvel élément */
                const prototype = $($blockSelector).data('prototype');
                $($blockSelector).append(prototype.replace(/__name__/g, $itemIndex));
                /* Copier des valeurs de champs sur le nouveau sous-formulaire */
                if (null != $copyValues) {
                    for (var $key in $copyValues) {
                        $($itemSelector + '_' + $key).val($($copyValues[$key]).val());
                    }
                }
                /* Configure les éventuels boutons ajoutés par le sous-formulaire */
                if (null != $additionSelector) {
                    setupAddButtons($itemSelector + ' ' + $additionSelector);
                }
                if (null != $deletionSelector) {
                    setupDeleteButtons($itemSelector + ' ' + $deletionSelector);
                }
            });
        }
    }

    /* Configure les boutons existants */
    function setupButtons()
    {
        if (null != $additionSelector) {
            setupAddButtons($additionSelector);
        }
        if (null != $deletionSelector) {
            setupDeleteButtons($deletionSelector);
        }
    }

    /* Vérifier qu'exactement une occurrence du bloc est présente */
    if (1 != $($blockSelector).length) {
        console.error($blockSelector, 'not found or not unique')
    }

    return setupButtons;
}
