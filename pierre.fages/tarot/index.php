<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="css/styles.css">
    <!-- UIkit CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uikit@3.4.2/dist/css/uikit.min.css" />

    <!-- UIkit JS -->
    <script src="https://cdn.jsdelivr.net/npm/uikit@3.4.2/dist/js/uikit.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/uikit@3.4.2/dist/js/uikit-icons.min.js"></script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

    <script src="js/tarot.js"></script>
    <script src="js/tools.js"></script>
</head>

<body>
    <div>
        <div class="uk-grid-collapse uk-child-width-expand@s uk-text-left" uk-grid>
            <div class="uk-width-2-3@m">
                <div class="uk-background-muted uk-padding">
                    <fieldset style="background-color: beige;">
                        <legend>Ajout de joueur (Par defaut: Mamy, Mam's, Nanie, Lili, Titi, Pap's, Marraine)</legend>
                        <input type="text" id="AddJoueursManuel" maxlength="8" size="8" placeholder="-">
                        <button id="AddNewJoueur" class="uk-button uk-button-primary">Add</Button>
                    </fieldset>
                </div>
            </div>
            <div class="uk-width-1-3@m">
                <div class="uk-background-muted uk-padding">
                    <fieldset style="background-color: chartreuse;">
                        <legend>Pris en compte:</legend>
                        <div id="AddNewJoueurDisplay"></div>
                    </fieldset>
                </div>
            </div>
        </div>
    </div>

    <table id="displayTarot" style="width: 100%">
        <thead>
            <tr>
                <th>#</th>
                <th>Joueur1</th>
                <th>Joueur2</th>
                <th>Joueur3</th>
                <th>Joueur4</th>
                <th>Joueur5</th>
                <th>Check</th>
            </tr>
        </thead>
        <tbody>
            <tr id="joueurs">
                <td><button id="ResetGame" class="uk-button uk-button-danger">Reset Score</button></td>
                <td><select id="Joueur_0"></select></td>
                <td><select id="Joueur_1"></select></td>
                <td><select id="Joueur_2"></select></td>
                <td><select id="Joueur_3"></select></td>
                <td><select id="Joueur_4"></select></td>
                <td id="JoueursCheck"></td>
            </tr>
            <!--            <tr id="Resultat_0">
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td> 
</tr>-->
            <tr id="Prise">
                <td>Qui a pris</td>
                <td><input id="Prise_0" type="checkbox" /></td>
                <td><input id="Prise_1" type="checkbox" /></td>
                <td><input id="Prise_2" type="checkbox" /></td>
                <td><input id="Prise_3" type="checkbox" /></td>
                <td><input id="Prise_4" type="checkbox" /></td>
                <td></td>
            </tr>
            <tr id="Partenaire">
                <td>Partenaire</td>
                <td><input id="Partenaire_0" type="checkbox" /></td>
                <td><input id="Partenaire_1" type="checkbox" /></td>
                <td><input id="Partenaire_2" type="checkbox" /></td>
                <td><input id="Partenaire_3" type="checkbox" /></td>
                <td><input id="Partenaire_4" type="checkbox" /></td>
                <td></td>
            </tr>
            <tr id="Poignee">
                <td>Poignee</td>
                <td><select id="Poignee_0"></select></td>
                <td><select id="Poignee_1"></select></td>
                <td><select id="Poignee_2"></select></td>
                <td><select id="Poignee_3"></select></td>
                <td><select id="Poignee_4"></select></td>
                <td></td>
            </tr>
        </tbody>
    </table>
    <br />

    <table>
        <tr>
            <td style="background-color: honeydew;" class="Calcul"> 
                Calcul
            </td>
            <td class="Calcul">
                <table>
                    <tr>
                        <td class="Calcul">Annonce?</td>
                        <td colspan="4">
                            <select name="Select1" id="idAnnonce">
                                <option value="0" selected>Petite</option>
                                <option value="1">Pouce</option>
                                <option value="2">Garde</option>
                                <option value="3">Garde Sans</option>
                                <option value="4">Garde Contre</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td rowspan="2" class="Calcul">Nb point?</td>
                        <td colspan="2" class="Calcul">Du Preneur?</td>
                        <td colspan="2" class="Calcul">Du defenseur?</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="Calcul"><input name="Text1" type="text" id="nbPointPreneur" maxlength="2" size="2" placeholder="-"/></td>
                        <td colspan="2" class="Calcul"><input name="Text1" type="text" id="nbPointDefenseur" maxlength="2" size="2" placeholder="-"/></td>
                    </tr>
                    <tr>
                        <td rowspan="2" class="Calcul">Nb bout?</td>
                        <td rowspan="2">
                            <select name="Select1" id="nbBout">
                                <option value="0" selected>-</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                            </select>
                        </td>
                        <td rowspan="2" class="Calcul">Petit au bout?</td>
                        <td class="Calcul">Du Preneur?</td>
                        <td class="Calcul">Du defenseur?</td>
                    </tr>
                    <tr>
                        <td style="text-align: center; vertical-align: middle;"><input type="checkbox" id="rPetitBout_Preneur" /></td>
                        <td style="text-align: center; vertical-align: middle;"><input type="checkbox" id="rPetitBout_Defenseur" /></td>
                    </tr>
                </table>
            </td>
            <td style="background-color: honeydew;">
                <button id="Submit" class="uk-button uk-button-primary">Go!</button>
            </td>
            <td style="background-color: #EFE4E2;">
                <button id="Oups" class="uk-button uk-button-secondary">Oups!</button>
            </td>
        </tr>
    </table>

    <div id="divDetailCalcul">
    </div>
</body>

<script>
Go()
</script>

</html>