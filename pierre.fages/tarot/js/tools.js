"use strict";

// -----------------------------------
// dans les steing de la forme tototo_5
// retrouver le int apres le _
// -----------------------------------
function getInputIndex (sString) {
    let charToFind = '_';
    let indexOfChetToFind = sString.indexOf(charToFind);
    if (indexOfChetToFind < 0) {
        return -1;
    }

    indexOfChetToFind++;

    let subString = sString.substring(indexOfChetToFind, sString.length);
    return (parseInt(subString, 10));
}


// -----------------------------------
// dans la liste des checkbox de le 'PRISE' retourver l'index de celle checked
// et indirecteent donc du user
// -----------------------------------
function getIndexPreneur () {
    let allJoueurCheckBox=$("#Prise > td > input");
    let rc=-1;
    allJoueurCheckBox.each(function (index) {
        if (rc == -1) {
            let me = $(this);
            if (me.is(':checked')) {
                let id = me.attr('id');
                rc=getInputIndex(id);
            }
        }
    });
    return rc;
} 

// -----------------------------------
// dans la liste des checkbox de le 'PATENAIRE' retourver l'index de celle checked
// et indirecteent donc du user
// -----------------------------------
function getIndexPartenaire() {
    let allPartenaireCheckBox = $("#Partenaire > td > input");
    let rc = -1;
    allPartenaireCheckBox.each(function (index) {
        if (rc == -1) {
            let me = $(this);
            if (me.is(':checked')) {
                let id = me.attr('id');
                rc = getInputIndex(id);
            }
        }
    });
    return rc;
} 



// -----------------------------------
// trouver le nom d'un jour par son index dans le DOM
// -----------------------------------
function getJouerParIndex(index) {
    let JoueurID = `#Joueur_${index}`;
    return $(JoueurID).val();
}

// -----------------------------------
// l'inverse Nom -> index
// -----------------------------------
function getIndexJoueur(Joueur) {
    let rc = -1;
    $('#joueurs > td > select').each(function (index) {
        if ($(this).val() == Joueur) {
            rc = index;
        }
    });
    return rc;
}

// ------------------------------
// est ce que le petit a ete mene au bout?
// ------------------------------
function isPetitAuBout() {
    let bRetour = $('#rPetitBout_Preneur').is(':checked');
    bRetour = bRetour || $('#rPetitBout_Defenseur').is(':checked');
    return bRetour;
}

// ------------------------------
// par le preneur?
// ------------------------------
function isPetitAuBoutPourLePreneur() {
    return $('#rPetitBout_Preneur').is(':checked');
}

// ------------------------------
// est ce qu'il y a une poignee?
// ------------------------------
function isUnePoignee() {
    let allPoigneeCombo = $("#Poignee > td > select");
    let rc = false;
    allPoigneeCombo.each(function (index) {
        if (parseInt($(this).val(), 10) > 0) {
            rc = true;
        }
    });
    return rc;
}

// ------------------------------
// son type simple, double, ...
// ------------------------------
function getTypePoignee() {
    let allPoigneeCombo = $("#Poignee > td > select");
    let rc = 0;
    allPoigneeCombo.each(function (index) {
        if (parseInt($(this).val(), 10) > 0) {
            rc = parseInt($(this).val(), 10);
        }
    });
    return rc;
}


// -----------------------
// mise a jour des select des joueurs
// -----------------------
function miseAjourDesSelecteurDeJoueurs () {
    $("#joueurs > td > select > option").remove();

    $("#joueurs > td > select").each(function (index) {
        let me = $(this);
        let options = me.find("option");
        if (options.length == 0) {
            aJoueurs.forEach(function (item, index, array) {
                me.append($('<option>', {
                    value: item,
                    text: aAliasJoueur[index]
                }));
            });
            me.change(OnJoueurChange);
        }
    });
}