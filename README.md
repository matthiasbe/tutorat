## Site web du tutorat de médecine KB

Application web utilisant le framework [Fat Free Framework](http://fatfreeframework.com/) pour répondre aux besoins Backend et le framework BootStrap pour répondre aux besoins Frontend.

### Fonctionnalités:
* Connexions/inscriptions
* création des comptes à partir d'une liste excel
* Rédaction collaborative de QCM
  * Proposition d'une question
  * Acceptation des question et organisation du QCM
  * Recherche de question
  * Exportation PDF

### Organisation des dossiers du projet:
* Le fichier `index.php` est le point de départ de l'application, page d'accueil du site internet. Il sert à initialiser le framework et a router vers les différentes pages.
* Le dossier `classes` contient le code PHP du projet. Chaque sous-dossier correspond à une page, découpée selon le modèle [MVC](https://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller)
  * `View` correspond au contrôleur de la page : il fait le lien avec la vue
  * `Manager` correspond à l'accesseur physique : il permet de récupérer les données dans la base de données SQL
  * `Data` correspond au modèle des données de la page. Il est remplis par le Manager et fournit au contrôleur.

Parmi les classes PHP on retrouve également des classes permettant des fonctionnalités transverses ainsi que le dossier `Modèle` qui correspond à un modèle de classe `Data` et `Manager`, permettant de factoriser le code commun aux différentes pages.

* Le dossier `css` contient les feuilles de styles
* Le dossier `files/images` contient les images du projet
* Le dossier `fonts` contient les différentes polices
* Le dossier `js` contient les scripts javascript utilisés dans les différentes pages
* Le dossier `lib` contient les fichiers du framework PHP FatFree
* Le dossier `templates` contient les différentes page html du site internet
