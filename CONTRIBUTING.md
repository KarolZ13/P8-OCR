# Contribution au projet

***NB:** Toutes les modifications ou créations de fonctions ainsi que les noms des branches et des commits doivent être en anglais.*

*Il n'y a que les commentaires dans le code ainsi que les fonctions de tests qui peuvent être en francais.*

**Créez une Nouvelle Branche :**

```
git checkout -b features\name_of_the_branch
```

**Effectuez les Modifications :**

Modifiez les fichiers nécessaires.
Assurez-vous que les modifications fonctionnent correctement.
Commit et Push :

```
git commit -m "Description"
git push origin features\name_of_the_branch
```

**Pull Request :**

Créez une pull request pour intégrer vos modifications.
Qualité et Règles à Respecter
Règles de Codage :

```
Symfony 6: Conventions Symfony
Normes PSR 1, 2 et 12.
Utilisez des noms de variables et de fonctions significatifs.
```

**Tests Unitaires :**

Écrivez des tests unitaires exhaustifs et vérifiez le rapport de couverture de code :

```
vendor/bin/phpunit --coverage-html public/test-coverage
```

Ensuite accèder à la page de coverage qui est l'ip de l'hôte suivie de "/test-coverage/":

```
http://127.0.0.1:8000/test-coverage/
```

**Revues de Code :**

Avant la fusion, demandez une revue de code.

**Audit de qualité du code :**

Verifiez le resultat de l'analyse Codacy

**Documentation :**

Documentez vos modifications.
Ajoutez des commentaires pertinents dans le code.