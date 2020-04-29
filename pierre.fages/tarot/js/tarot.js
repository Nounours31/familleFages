"use strict";

var aJoueurs = ['-', 'Maman', 'Melanie', 'Pauline', 'Thibault', 'Papa'];
var aAliasJoueur = ['-', "Mam's", "Nanie", 'Lili', 'Titi', "Pap's"];

var aPoignee = ['-', 'Simple', 'Double', 'Triple'];

function Go() {
    // -----------------------------------------
    // branchement des evenements
    // -----------------------------------------

    // ajout d'un nouveau jouer, il faut mettre  ajour les combo de selection
    $('#AddNewJoueur').click(onAddNewJoueur);

    // reset du jeu en cours = reset des scrore en DB + a l'ecran
    $("#ResetGame").click(onResetGame);

    // branchement sur un event applicatif pour lors du calcul du score un emise  ajour de l'affichage
    $("#displayTarot").on("onNewScoreUpdateTable", {}, onNewScoreUpdateTable);

    // callback de qui prend la partie (check user exist et cacher les autre choix)
    $("#Prise  > td > input").each(function (index) {
        let me = $(this);
        $(this).show();
        me.change(onPriseChange);
    });

    // call back du partenaire a affiche que si 5 joueurs et un preneur
    $("#Partenaire > td > input").each(function (index) {
        $(this).hide();
        $(this).change(onPartenaireChoisi);
    });


    // -----------------------------------------
    // Init de la page
    // -----------------------------------------
    // mise  ajour des combo de joueur
    miseAjourDesSelecteurDeJoueurs();

    // -----------------------
    // Poignee + son call back
    // -----------------------
    $("#Poignee > td > select").each(function (index) {
        let me=$(this);
        let options = me.find("option");
        if (options.length == 0) {
            aPoignee.forEach(function (item, index, array) {
                me.append($('<option>', {
                    value: index,
                    text: item
                }));
            });
            $(this).change(OnPoigneeChange);
        }
    });

    // -----------------------
    // Petit: exclusion mutuelle du petit au bout
    // -----------------------
    $("#rPetitBout_Preneur").click (function () {
        if ($(this).is(':checked')) {
            $("#rPetitBout_Defenseur").prop("checked", false);
        }
    });
    $("#rPetitBout_Defenseur").click(function () {
        if ($(this).is(':checked')) {
            $("#rPetitBout_Preneur").prop("checked", false);
        }
    });

    // -----------------------
    // call back du submit
    // -----------------------
    $("#Submit").click(onSubmit);
}



// -------------------------------------------------
// Ajouter un nouveau jour = mettre a jour la liste des joueurs + refresh des select
// -------------------------------------------------
function onAddNewJoueur(event, handler) {
    let nom=$('#AddJoueursManuel').val();
    aJoueurs.push(nom);
    aAliasJoueur.push(nom);

    $('#AddNewJoueurDisplay').append('<br/>'+nom);

    // mise  ajour des select
    miseAjourDesSelecteurDeJoueurs();
}



// -------------------------------------------------
// Reset du jeu =
//  1. reset de la db de ces jouers
//  2. clean de l'UI avec les resultats
// -------------------------------------------------
function onResetGame(event, handler) {
    // nb joueur
    let nbJoueur = parseInt($("#JoueursCheck").text(), 10);


    // Reset de la DB en Ajax
    let JSONForAjax = {
        "type": "putDBInfo",
        "message": []
    };
    for (let i = 0; i < nbJoueur; i++) {
        let nom = getJouerParIndex(i);
        let jsonmessage = {
            "nom": nom,
            "score": 0.0
        }
        JSONForAjax.message.push(jsonmessage);
    }
    let jsonAsString = JSON.stringify(JSONForAjax);

    $.ajax({
        type: "POST",
        url: "php/db.php",
        async: false,
        data: jsonAsString,
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        success: function (data) { console.log("Ajax OK " + data); },
        failure: function (errMsg) {
            alert(errMsg);
        }
    });    


    // Clean de l'UI
    let allLigne = $("tr[name='LigneResultat']");
    allLigne.remove();
}



// -------------------------------------------------
// Un partie est finie - calcul du score affichage dans 'UI
//  1. mise  ajour de la DB avec ce nouveau score
//  2. Update des resultats UI
// -------------------------------------------------
function onNewScoreUpdateTable (event, handler) {
    // nb joueur
    let nbJoueur = parseInt($("#JoueursCheck").text(), 10);
    

    // Update des donnes en DB
    let JSONForAjax = {
        "type": "GetDBInfo",
        "message": []
    };
    for (let i = 0; i < nbJoueur; i++) {
        let nom = getJouerParIndex(i);
        JSONForAjax.message.push(nom);
    }

    let jsonAsString = JSON.stringify(JSONForAjax);
    let jsonASresult = {};
    $.ajax({
        type: "POST",
        url: "php/db.php",
        async: false,
        data: jsonAsString,
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        success: function (data) { console.log("Ajax OK " + data); jsonASresult = data; },
        failure: function (errMsg) {
            alert(errMsg);
        }
    });


    // -----------------------------------------------------
    // mise  ajour de l'UI
    // -----------------------------------------------------
    
    // mettre en petit les anciennes lignes
    $("tr[name='LigneResultat']").css("font-size", "60%");
    $("tr[name='LigneResultat']").css("background-color", "");


    // mettre le nouveau score
    let newTR = "<tr name=\"LigneResultat\" style=\"background-color: palegreen;\"><td>Score</td>";
    let check=0.0;
    let AllJoueurOK = false;
    let indexJoueurToFill = 0;
    while (!AllJoueurOK) {
        jsonASresult.forEach(element => {
            let nomJoueur = element.nom;
            let score = element.score;
            let indexJoueur = getIndexJoueur(nomJoueur);
            if (indexJoueurToFill == indexJoueur) {
                newTR += "<td>"+score+"</td>";
                indexJoueurToFill++;
                check += parseFloat(score);
            }
        });
        if (indexJoueurToFill >= nbJoueur) {
            AllJoueurOK = true;
            while (indexJoueurToFill < 5) {
                newTR += "<td></td>";
                indexJoueurToFill++;
            }
        }
    }
    newTR += "<td> " + check + "</td></tr>";
    $(`${newTR}`).insertBefore($("#Prise"));
}


// ---------------------------------------
// on vient de changer de joueur
// Est ce que ce jour existe deja ?
// ---------------------------------------
function OnJoueurChange(eventData, handler) {
    let sender=$(this);
    let nomChoisi = sender.find("option:selected").val();
    if (nomChoisi == '-')
        return;
        
    // check pas deja un joueur du meme nom
    $("#joueurs > td > select").each(function (index) {
        let me = $(this)
        if (sender.attr('id') == me.attr('id')) {
            console.log ('je suis sur le meme joueur');
        }
        else {
            let monNom = me.find("option:selected").val();
            if ((monNom != null) && (monNom == nomChoisi)) {
                alert ('Nom deja choisi');
                sender.val('-');
            }
        }
    });

    // mise a jour du NB jour de la partie
    let nbJoueur = 0;
    $("#joueurs > td > select").each(function (index) {
        let me = $(this)
        let nomChoisi = me.find("option:selected").val();
        if (nomChoisi != '-')
            nbJoueur++;
    });
    $("#JoueursCheck").html(nbJoueur);

    // nettoyage des score de tous les joueur -- nouvelle partie
    $("#ResetGame").trigger("click");
}





// ------------------------------------------------
// Qui a pris ?
// ------------------------------------------------
function onPriseChange(eventData, Handler) {
    let domEmetteur = eventData.target;
    let idDomEmetteur = domEmetteur.id;

    // est ce un evenement de check?
    // sur du uncheck rafficher les checkbox cachee ... et cacher les partenaire
    let jEmetteur = $(`#${idDomEmetteur}`);
    if (!jEmetteur.is(':checked')) {
        $("#Prise > td > input").each(function (index) {
            $(this).show();
        });

        $("#Partenaire > td > input").each(function (index) {
            $(this).hide();
            $(this).prop("checked", false);
        });

        return true;
    }  
    
    
    // est ce que mon joueur existe ?
    let indiceJoueur = getInputIndex(idDomEmetteur);
    let joueurNom = $(`#Joueur_${indiceJoueur}`).val();
    if (joueurNom == '-') {
        alert("Pas de joueur associe");
        eventData.originalEvent.stopImmediatePropagation();
        jEmetteur.prop("checked", false);
        return false;
    }


    // cacher les autre checkbox
    $("#Prise > td > input").each(function (index) {
        let me = $(this);
        if (me.attr('id') == idDomEmetteur) {
            console.log('je suis sur le meme joueur');
        }
        else {
            if (me.is (':checked')) {
                alert ("Quelqu'un d'autre a deja pris");
            }
            else {
                me.hide();
            }
        }        
    });

    // affichage des partenaires possibles
    // si plus de 5 joueurs
    let nbJoueur = parseInt($("#JoueursCheck").text(), 10);
    if (nbJoueur > 4) {
        $("#Partenaire > td > input").each(function (index) {
            $(this).show();
        });
    }
}
   



// ------------------------------------------------
// Qui a pris ?
// ------------------------------------------------
function onPartenaireChoisi(eventData, Handler) {
    let domEmetteur = eventData.target;
    let idDomEmetteur = domEmetteur.id;

    // est ce un evenement de check?
    // sur du uncheck ne rien faire
    let jEmetteur = $(`#${idDomEmetteur}`);
    if (!jEmetteur.is(':checked')) {
        return true;
    }

    // est ce que mon joueur existe ?
    let indiceJoueur = getInputIndex(idDomEmetteur);
    let joueurNom = $(`#Joueur_${indiceJoueur}`).val();
    if (joueurNom == '-') {
        alert("Pas de joueur associe");
        eventData.originalEvent.stopImmediatePropagation();
        jEmetteur.prop("checked", false);
        return false;
    }


    // cacher les autre checkbox
    $("#Partenaire > td > input").each(function (index) {
        let me = $(this);
        if (me.attr('id') == idDomEmetteur) {
            console.log('je suis sur le meme joueur');
        }
        else {
            if (me.is(':checked')) {
                alert("Quelqu'un d'autre a deja pris");
            }
            else {
                me.hide();
            }
        }
    });
}


// --------------------------------------------------------
// selection d'une poignee, il doit juste y avoir un joueur associe a la poignee
// --------------------------------------------------------
function OnPoigneeChange(eventData, Handler) {
    let domEmetteur = eventData.target;
    let idDomEmetteur = domEmetteur.id;
    let jEmetteur = $(`#${idDomEmetteur}`);

    // ne rien faire sur une non selection
    let nomChoisi = jEmetteur.find("option:selected").val();
    if (nomChoisi == '-')
        return;

    // est ce que mon joueur associe existe ?
    let indiceJoueur = getInputIndex(idDomEmetteur);
    let joueurNom = $(`#Joueur_${indiceJoueur}`).val();
    if (joueurNom == '-') {
        alert("Pas de joueur associe");
        eventData.originalEvent.stopImmediatePropagation();
        jEmetteur.val('-')
        return false;
    }
}



// --------------------------------------------------------
// Calcul des points
// --------------------------------------------------------
function onSubmit(eventData, Handler) {
    let idDivDetail ='divDetailCalcul';
    let msg = '';
 
    let aMinumunPoint = [56, 51, 41, 36]; // 0 bout, 1, 2, 3
    let aPrimePoignee = [0, 20, 30, 40]; // simple, double, triple
    let aCoefMultiplicateurAnnonce = [1, 1, 2, 4, 6]; // petite, pouce, garde, garde s, garde c

    // nb joueur
    let nbJoueur = parseInt ($("#JoueursCheck").text(), 10);
    msg += `<br/>il y a: ${nbJoueur} <br/>`;

    let aEquipePreneur = [];
    let aEquipeDefense = [];
    let aScore = [0, 0, 0, 0, 0, 0];

    // -------------------------
    // recherche de l'equipe preneuse et defensive
    // -------------------------
    // preneur
    let indexPreneur = getIndexPreneur();
    if (indexPreneur < 0) {
        alert ("Il doit y avoir un preneur selectionne");
        return false;
    }
    msg += `Preneur: ${indexPreneur} <br/>`;
    aEquipePreneur.push(indexPreneur);

    // partenaire
    let indexPartenaire = -1;
    if (nbJoueur == 5) {
        indexPartenaire = getIndexPartenaire();
        if (indexPartenaire < 0) {
            alert("Il doit y avoir un partenaire selectionne");
            return false;
        }
        // cas ou a 5 on appelle son propre roi
        if (indexPartenaire != indexPreneur) {
            aEquipePreneur.push(indexPartenaire);
        }
        else {
            indexPartenaire = -1;
        }
        msg += `Partenaire: ${indexPartenaire} <br/>`;
    }

    // defense
    for (let i = 0; i < nbJoueur; i++) {
        if (aEquipePreneur.indexOf(i) < 0) {
            aEquipeDefense.push(i);
        }
    }


    // -------------------------
    // decompte des point
    // -------------------------
    let NbPoint = parseInt ($("#nbPointPreneur").val(), 10);
    if ((NbPoint == 0) || (isNaN(NbPoint))) {
        NbPoint = 91 - parseInt($("#nbPointDefenseur").val(), 10);
    }
    if (isNaN(NbPoint)) {
        Alert ("Nombre de point invalide");
        return false;
    }
    msg += `NbPoint: ${NbPoint} <br/>`;


    // -------------------------
    // Status du contrat chute - fait ?
    // -------------------------
    let NbBout = $("#nbBout").val();
    let contrat = NbPoint - aMinumunPoint[NbBout];
    msg += `contrat: ${contrat} <br/>`;


    let bContratFait = false;
    if (contrat > 0) {
        bContratFait = true;
    }

    // -------------------------------
    // score
    // -------------------------------
    let score = 25 + Math.abs(contrat);
    if (!bContratFait) {
        score = -score;
    }
    msg += `score du contrat: ${score} <br/>`;


    // -------------------------
    // le petit
    // -------------------------
    if (isPetitAuBout()) {
        if (isPetitAuBoutPourLePreneur()) {
            score += 10;
        }
        else {
            score -= 10;
        }
    }
    msg += `score apres petit: ${score} <br/>`;

    // -------------------------
    // l'annonce
    // -------------------------
    let coefAnnonce = aCoefMultiplicateurAnnonce[$("#idAnnonce").val()];
    score *= coefAnnonce;
    msg += `score avec coeff: ${score} <br/>`;

    // -------------------------
    // la poignee
    // En Défense, la Poignée est solidaire, 
    //     la marque de chaque joueur de la Défense devant être identique.
    // La poignée est donc comptée (en plus ou en moins) au camp qui la présente  (ou le Preneur ou la Défense). 
    // Le camp qui présente une Poignée en bénéficie en cas de gain, 
    //     mais c’est son adversaire qui en bénéficie en cas de perte de ce camp.
    // -------------------------
    if (isUnePoignee()) {
        let typeDePoignee = getTypePoignee();
        if (bContratFait) {
            score += aPrimePoignee[typeDePoignee];
        }
        else {
            score -= aPrimePoignee[typeDePoignee];
        }
    }
    msg += `score avec poignee: ${score} <br/>`;


    // -------------------------
    // Repartition des points par joueurs
    // -------------------------
    for (let i = 0; i < nbJoueur; i++) {
        if (nbJoueur == 4) {
            if (i == indexPreneur) {
                aScore[i] = 3 * score;
            }
            else {
                aScore[i] = -score;
            }
        }
        else if (nbJoueur == 5) {
            if (i == indexPreneur) {
                if (indexPartenaire < 0) { // pas de parenaire
                    aScore[i] = 4 * score;
                }
                else {
                    aScore[i] = 2 * score;
                }
            }
            else if (i == indexPartenaire) {
                aScore[i] = score;
            }
            else {
                aScore[i] = -score;
            }
        }
    }
    msg += `resultat: [${aScore[0]}, ${aScore[1]}, ${aScore[2]}, ${aScore[3]}, ${aScore[4]}] <br/>`;

    // -------------------------
    // Envoie du score en DB
    // -------------------------
    let JSONForAjax = {
        "type": "addDBInfo",
        "message": []
    };
    for (let i = 0; i < nbJoueur; i++) {
        let nom = getJouerParIndex (i);
        let localObj = {
            "nom": nom,
            "score": aScore[i]
        }
        JSONForAjax.message.push (localObj);
    }
    let jsonAsString = JSON.stringify(JSONForAjax);

    msg += jsonAsString;
    $(`#${idDivDetail} `).html(msg);

    $.ajax({
        type: "POST",
        url: "php/db.php",
        async: false,
        data: jsonAsString,
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        success: function (data) { console.log ("Ajax OK " + data); },
        failure: function (errMsg) {
            alert(errMsg);
        }
    });

    // -------------------------
    // mettre dans l'UI le detail du calcul - les points attention ce n'est pas le score
    // -------------------------
    let check = 0.0;
    let indexJoueurToFill=0;
    let newTR = "<tr name=\"LigneResultat\" style=\"font-size: 60%; text-align: right\"><td>Element de calcul</td>";
    for (let i = 0; i < nbJoueur; i++) { 
        newTR += "<td>" + aScore[i] + "</td>";
        indexJoueurToFill++;
        check += parseFloat(aScore[i]);
    }
    while (indexJoueurToFill < 5) {
        newTR += "<td></td>";
        indexJoueurToFill++;
    }
    newTR += "<td> " + check + "</td></tr>";
    $(`${newTR}`).insertBefore($("#Prise"));


    // -------------------------
    // Envoie envt user de mise  ajour de l'UI
    // -------------------------
    $("#displayTarot").trigger("onNewScoreUpdateTable");

    // -------------------------
    // reset de l'UI
    // -------------------------
    // clean checkbox de qui a pris
    $('#Prise > td > input').each(function (index) {
        let me = $(this);
        me.prop('checked', false);
        me.show();
    });
    
    // clean checkbox partenaire
    $('#Partenaire > td > input').each(function (index) {
        let me = $(this);
        me.prop('checked', false);
        me.hide();
    });

    // reset nb bout
    $('#nbBout').val(0);

    // reset petit au bout
    $("#rPetitBout_Defenseur").prop("checked", false);
    $("#rPetitBout_Preneur").prop("checked", false);

    // reset des poignee
    $("#Poignee > td > select").each(function (index) {
        let me = $(this);
        me.val(0)
    });
}