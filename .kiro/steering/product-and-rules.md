# ThisProject Product Summary

## What it is

Powerful PHP backend wrapper for yt-dlp with automatic binary management

### Strict Requirements

This project uses:

- **English**: All code and comments must be in English
- **Avoid comments**: All code must be commented only with parameters and return with type (follow PHPStan level 6). Use comments only if it is very complete
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
