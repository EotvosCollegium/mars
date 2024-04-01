# Contributing guidelines

## Official documentations
- [Laravel](https://laravel.com/)
- [Materialize](https://materializecss.com/)

# GitHub, Git, Branching

- We organise our work around issues. Issues for structural changes should be discussed beforehand.
- We make a new branch for every issue (or connected issues).
- We open pull requests to the "development" branch.
- We test the new features on the "staging" branch
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

Database:

- Use migrations and factories for database structure and population.
- Avoid using Database triggers; opt for Laravel's observers.
- Use database constraints as much as possible within reasonable limits. This usually includes foreign and composite keys.
- We prefer a separate table for enum-like values over enum type fields for easier value modification.

Laravel Features:

- Use policies instead of gates.
- Use observers instead of events or triggers.
- Use attributes for custom getters/setters.

Structure, code style:

- Utilize resources whenever possible, following the index-create-store-show-edit-update-destroy naming convention if applicable.
- Refrain from adding logic to the User model; delegate logic to other models where possible.
- Limit controller actions to 6-7 statements and one conditional.
- Use `$request->validate(...)` for validation.
- Add more comments than anticipated.
- Include return types for IDE's type hints.
- Prioritize readability.
- Remember: Code should be understandable by freshmen years later.

Translation:

- Translate pages accessible by guests.
- Use Hungarian for admin pages.
- Translating pages available to collegists are currently undecided.

Testing:

- Ensure every controller action has at least one trivial test to aid future refactorings and reveal regressions.
- Black box test non-trivial logic (feature test) and white box test calculations (testing every possible case).
- Test migrations that modify existing data manually; for critical cases, the maintaners should test with real data.


Note: The current project state may not adhere to all guidelines; aim for incremental implementation.