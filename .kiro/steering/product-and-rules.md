# ThisProject Product Summary

## What it is
This project is an AI‑powered research and note‑taking tool. It lets you upload sources like PDFs, docs, websites, and videos, then summarizes them, explains concepts, and helps you generate insights based on your own materials. 
It can generate new content, such as text and images, blog posts, articles, recipes, etc., based on user information on content table, and can connect to other services to publish content on them.

### Strict Requirements

This project uses:
- Backend: Laravel 12, PostgreSQL.
- Frontend: Vue with Inertia, Vue Composition API, TypeScript, Tailwind, and the reka-ui component library
  - Reka UI components docs: https://reka-ui.com/docs/overview/getting-started
  - The interface should have the correct spacing, not exceeding size 4. Only use more than 4 if absolutely necessary.
  - For translate on Vue, use "const { t, getGroup } = useTranslations();"

- **English**: All code and comments must be in English
- **Avoid comments**: All code must be commented only with parameters and return with type (follow PHPStan level 6). Use comments only if it is very complete
- **Locale/Translate**: Never use hardcoded text. Always check the "lang" language folder to see if it already exists, or create it if it doesn't, for all languages ​​in the folder in the corresponding file
- **Database**: 
  - This project uses PostgreSQL with Vector column. Always use the vector column when you need to optimize, especially for searching. Use "pg_trgm" for better search performance.
  - Never delete, fresh or clear the database. Always use migrations to add or remove tables and columns.
  - Every column created in the database must be added to $cast with the correct type in your "model". And it must have its "@property-read" with its type added in the class doc.
- **Eloquent**: When creating an Eloquent method, always use the "query()" pattern. Example: "User::query()->where()"
- **Code**:
  - All code must be formatted with Pint
  - DRY code (Don't Repeat Yourself)
    - Search for methods, function, class and use services to not repeat code
  - User or crete Enum on enum folder
- **Code consistency**: Follow the equivalent file format to maintain consistency
- **Strict types**: Every PHP file MUST start with `declare(strict_types=1);`
- **PSR-12**: All code must follow PSR-12 standards enforced by Pint
- **Type hints**: Always use explicit return types and parameter types. Always add the data types of constants, variables, parameters, and function return values.
- **PHPStan Level 6**: Code must pass static analysis without errors
- **No inline validation**: Always use Form Request classes for validation
- **Documentations and ".md" files**: Never create documentation or ".md" without authorization
- **Services**: (DRY code) The main services file should be located in the services folder, the other files are located in subfolders with the same name as the service, and the main file calls the files in the subfolder as needed.
- **Tools**: The tools should have their responsibilities separated into a "handlers" folder named after the tool. Only the calls to the handlers and the main function of the tool should remain in the main file.
