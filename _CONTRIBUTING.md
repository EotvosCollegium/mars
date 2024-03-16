# Contributing guidelines

## Official documentations
- [Laravel](https://laravel.com/)
- [Materialize](https://materializecss.com/)

# GitHub, Git, Branching

- We organise our work around issues. Issues for structural changes should be discussed beforehand.
- We make a new branch for every issue (or connected issues).
- We open pull requests to the "development" branch.
- We regularly update the "deployment" branch with changes on "development".
- We expect every PR to have an approved review from someone at least as experienced with Mars as the PR creator. 


## Simple git usage

```bash
# when you start working
git checkout development
git pull
git checkout -b your_feature_branch

# add your changes

# when you are done
git add --all  # or only your changes
git commit # an editor comes up, the first line should look like: Issue #x: changed this and that
# add more information if needed
git fetch origin
git rebase origin/master # resolve conflicts if something comes up
git push origin your_feature_branch

# open repo in your browser and you should see a Create PR option.
```

# Conventions

Laravel (or web apps in general) has multiple ways to achieve the same result. Now we try to list our conventions we try to stick to*.


Database:
- We use migrations and factories to structure and populate our database.
- We do not use Database triggers. Use Laravel's observers instead.
- We try to have as much Database constraints as possible within reasonable limits. That usually includes foreign and composite keys.
- We prefer a separate table for enum like values than enum type field. This allows us to modify the values more easily.
- 

Laravel features:
- Use policies instead of gates.
- Use observers instead of events or triggers.
- Use attributes for custom getters/setters.
- We authorise and validate every request.


Structure:
- Use resources if possible (https://laravel.com/docs/10.x/controllers#resource-controllers) and follow index-create-store-show-edit-update-destroy naming if it makes sense. 
- Avoid adding logic to the User model. Extract as much as possible to other models.

Translation:
- We use translation for pages that is accessible by guests.
- We use hungarian for admin pages.
- Translating pages available for collegists is currently undecided.

Testing:
- Every controller action should have at least one trivial test to help future refactorings and reveailing regressions.
- Every not trivial logic should be black box tested (feature test, checking input/output). Calculations should be white box tested (testing every possible case).
- Migrations that modify existing data should be tested manually. For critical cases, it should also be tested with real data by the maintainers.

Documentation, code style:
- Controller actions should not contain more than 6-7 statements and more than one conditional.
- For validation, we use `$request->validate(...)`.
- Add more comment than you might think needed.
- Add return types. That also helps the IDE's type hints.
- Focus on readability.
- Remember: your code should be understandable by freshmen years later.


*The current state of the project may not follow all of these guidelines. Achieving that should be done incrementally.
