<?php
// Le diagnostic du conseiller moyen : « Quel client êtes-vous ? ».
// Chaque réponse compte pour une variante (a, b, c, d) ; la plus fréquente l'emporte, l'égalité donne la variante E.
// Quel que soit le résultat, le client est moyen : seule la variante change.
return [
    'questions' => [
        ['Face à un problème, vous…', [
            'a' => 'demandez conseil', 'b' => 'attendez qu\'il parte tout seul',
            'c' => 'cherchez la notice', 'd' => 'le confiez au stagiaire']],
        ['Au guichet, vous…', [
            'a' => 'prenez un ticket', 'b' => 'prenez deux tickets, par sécurité',
            'c' => 'demandez s\'il existe une autre file', 'd' => 'attendez qu\'on vous appelle par votre prénom']],
        ['On vous donne un conseil absurde. Vous…', [
            'a' => 'le suivez', 'b' => 'l\'encadrez', 'c' => 'le contestez, puis le suivez', 'd' => 'le transférez à un ami']],
        ['Votre café, en général :', [
            'a' => 'tiède', 'b' => 'oublié sur un coin de bureau', 'c' => 'jamais commandé', 'd' => 'encore dans la machine']],
        ['Quand la photocopieuse se bloque, vous…', [
            'a' => 'appelez quelqu\'un', 'b' => 'attendez qu\'elle se débloque', 'c' => 'lisez le message d\'erreur', 'd' => 'changez d\'étage']],
        ['Votre signature ressemble à…', [
            'a' => 'votre nom, lisible', 'b' => 'un trait', 'c' => 'autre chose à chaque fois', 'd' => 'celle du cabinet']],
        ['Votre formule de fin de courriel :', [
            'a' => '« Bien à vous »', 'b' => '« Dans l\'attente »', 'c' => '« Cordialement (ou presque) »', 'd' => '« Moyennement vôtre »']],
    ],

    'profils' => [
        'a' => ['Le client moyen, variante A', "Optimisme prudent. Vous suivez les conseils avec confiance, mais en gardant un parapluie. Le cabinet vous recommande de continuer, à peu près."],
        'b' => ['Le client moyen, variante B', "Patience résignée. Vous attendez que les choses s'arrangent, et parfois elles s'arrangent. Le cabinet admire votre constance, de loin."],
        'c' => ['Le client moyen, variante C', "Scepticisme convaincu. Vous doutez de tout, sauf de vos doutes. Le cabinet vous recommande un conseil, puis de le contester."],
        'd' => ['Le client moyen, variante D', "Délégation sereine. Vous confiez vos problèmes aux autres, qui les confient au stagiaire. Le circuit est fermé, et il fonctionne."],
        'e' => ['Le client moyen, variante E', "Parfaitement moyen. Vos réponses se répartissent avec une régularité exemplaire. Le cabinet vous propose comme étalon officiel de la moyenne."],
    ],
];
