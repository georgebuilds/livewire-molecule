## Contributing

Thanks for helping improve Livewire Molecule. This project is a Laravel
Livewire wrapper around 3Dmol.js for rendering molecular structures.

### Development setup

- PHP 8.1+
- Composer
- Laravel 10/11/12 (for integration testing)

Install dependencies:

```
composer install
```

### Running tests

```
composer test
```

### Linting and static analysis

This package targets PHPStan level 610. Please run:

```
./vendor/bin/phpstan analyse 
```

You can lint your code with Pint

```
./vendor/bin/pint
```

### Coding standards

- Use explicit parameter, return, and property types.
- Prefer small, focused changes with clear names.
- Keep Blade and JS changes in sync with Livewire props.

### Pull requests

Please include:

- A brief summary of the change and why it is needed.
- Tests or a clear manual test plan.
- Documentation updates for any new options or behavior.

### Security

Do not include secrets or API keys. If you discover a security issue,
please open a private report or contact the maintainer directly.
