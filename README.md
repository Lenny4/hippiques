- Pour lancer le projet il faut lancer un serveur apache:

- Xampp par exemple

- puis se rendre sur localhost/

- Il y a 2 fichiers "importants" : chart.php et estimation.php

- chart.php:
  - permet juste d'afficher les graphes des différentes courses.
  - on peut changer les variables:
    - $nbMatch = 21 max (on a 21 matchs au maximum)
    - maxRunnerOdd = 10
    - avgBeforeTime = 2000
    - ratio = 0.01
    
- estimation.php, c'est la qu'il reste tout à faire:
  - permet juste d'afficher les graphes des différentes courses.
  - on peut changer les variables (actuellement):
    - $nbMatch = 21 max (on a 21 matchs au maximum)
    
- si tu veux trouver un algo qui fait des backs et des lays au bon moment créer toi ton propre fichier estimation2.php
