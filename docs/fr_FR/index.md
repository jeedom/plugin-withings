# Plugin Withings

## Description

Le plugin Withings synchronise dans Jeedom les données de santé disponibles sur le compte Withings associé : activité quotidienne, sommeil et mesures corporelles. Les données réellement disponibles dépendent des appareils et des autorisations accordées au compte Withings.

La synchronisation automatique est effectuée toutes les 30 minutes. La commande **Rafraîchir** d'un équipement permet aussi de lancer une synchronisation à la demande.

## Prérequis

- Un abonnement actif aux services cloud Jeedom est nécessaire.
- Aucun identifiant développeur Withings, *Client ID* ou secret OAuth n'est à créer ni à renseigner dans Jeedom : l'authentification et les appels à l'API Withings sont gérés par le cloud Jeedom.
- Un accès HTTPS public à votre Jeedom n'est nécessaire que si vous souhaitez activer le **mode push**.

## Création et liaison d'un équipement

1. Activez le plugin.
2. Ajoutez un équipement pour chaque utilisateur Withings à suivre.
3. Renseignez son nom, l'objet parent si besoin, puis activez et sauvegardez l'équipement.
4. Cliquez sur **Lier à un utilisateur**.
5. Une page d'authentification du cloud Jeedom s'ouvre dans un nouvel onglet. Connectez-vous à votre compte Withings et acceptez les autorisations demandées.

La liaison est propre à chaque équipement. Si plusieurs personnes utilisent le plugin, créez un équipement puis une liaison pour chacune d'elles.

## Mode push

Le mode push est facultatif. Lorsqu'il est activé, Withings notifie Jeedom dès qu'une donnée est disponible ; sinon les données sont récupérées lors de la synchronisation périodique.

Pour l'activer, cliquez sur **Activer** dans la section **Mode push** de l'équipement. Votre Jeedom doit alors être joignable depuis Internet en HTTPS et son accès externe doit être correctement configuré dans Jeedom. Cliquez sur **Désactiver** pour supprimer les abonnements push.

> **Important**
>
> Le mode push utilise l'URL publique de votre Jeedom. Une adresse locale, un accès externe non fonctionnel ou une redirection HTTPS incorrecte empêchent Withings de joindre Jeedom.

## Commandes

Les commandes créées par le plugin dépendent des données remontées par votre compte et vos appareils Withings.

| Catégorie | Commandes |
| --- | --- |
| Activité | Pas, Distance, Calories, Élévation |
| Sommeil | Durée du réveil, Temps pour dormir, Sommeil profond, Sommeil léger, Nombre de réveils |
| Mesures corporelles | Poids, Masse maigre, Ratio masse grasse, Masse grasse |
| Santé | Tension diastolique, Tension systolique, Rythme cardiaque, SpO2 |
| Action | Rafraîchir |

Les durées de sommeil sont exprimées en minutes, la distance en kilomètres et les unités des mesures sont indiquées sur chaque commande.

## Dépannage

- Vérifiez que l'équipement est activé et que la liaison Withings a bien été réalisée.
- Consultez le log `withings` pour connaître l'erreur retournée par le service cloud ou l'API Withings.
- En cas de problème de mode push, contrôlez en premier l'accessibilité HTTPS externe de Jeedom. La synchronisation périodique reste disponible sans mode push.
