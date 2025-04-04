# Contributing to GA4 Measurement Protocol Bundle

Thank you for considering contributing to the GA4 Measurement Protocol Bundle! We appreciate your time and effort to make this project better.

## Code of Conduct

This project adheres to a Code of Conduct. By participating, you are expected to uphold this code. Please report unacceptable behavior.

## How Can I Contribute?

### Reporting Bugs

Bugs are tracked as GitHub issues. Before creating bug reports, please check the existing issues to see if the problem has already been reported.

When creating a bug report, include as many details as possible:

- A clear and descriptive title
- Steps to reproduce the problem
- Expected behavior
- Actual behavior
- Symfony version
- PHP version
- Bundle version

### Suggesting Enhancements

Enhancement suggestions are also tracked as GitHub issues. When creating an enhancement suggestion, include:

- A clear and descriptive title
- Detailed explanation of the suggested enhancement
- Examples of how the enhancement would be used

### Pull Requests

1. Fork the repository
2. Create a new branch for your changes
3. Make your changes
4. Write or update tests for your changes
5. Ensure all tests pass
6. Submit your pull request

## Development Setup

1. Clone your fork of the repository
2. This project uses [Task](https://taskfile.dev) for running commands. If you don't have Task installed, you can [install it following their documentation](https://taskfile.dev/installation/).
3. Initialize the complete development environment with a single command:
   ```bash
   task init
   ```
   This will:
   - Start the Docker environment
   - Install all vendor dependencies
   - Set up everything needed for development

   If you don't have Task installed, you can do it manually:
   ```bash
   docker compose up -d
   # Wait for container to initialize and install Composer
   docker exec -it ga4-measurement-protocol-bundle-php composer install --no-interaction
   ```
4. The Docker environment will set up the `/dev/` directory with all necessary dependencies in the vendor directory

### Using Taskfile

This project uses a Taskfile (similar to Makefile but in YAML) to simplify common development tasks. Here are some useful commands:

- `task` - Show all available commands
- `task init` or `task i` - **Initialize complete development environment** (recommended for new users)
- `task vendor:install` or `task install` - Install vendor dependencies
- `task composer:dump-autoload` or `task autoload` - Update composer autoloader
- `task up` or `task u` - Start development environment 
- `task down` or `task d` - Stop development environment
- `task restart` or `task r` - Restart development environment
- `task php:shell` or `task sh` - Open shell in PHP container
- `task dev:serve` or `task serve` - Start development server
- `task test` or `task t` - Run PHPUnit tests
- `task test:all` or `task t:all` - Run tests for all Symfony versions
- `task stan` or `task lint` - Run PHPStan analysis
- `task cs:fix` or `task fix` - Fix code style

## Testing

This bundle supports multiple Symfony versions. Please test your changes across all supported versions:

```bash
# Run tests for all Symfony versions
task test:all

# Run tests for specific Symfony versions
task test:symfony54
task test:symfony64
task test:symfony71  # Requires PHP 8.2+
```

Note: Testing with Symfony 7.1 requires PHP 8.2 or higher. The main bundle is compatible with PHP 8.1 using Symfony 5.4 or 6.4.

## Coding Standards

This project follows Symfony coding standards. Before submitting your changes, run:

```bash
task cs:fix
```

## Static Analysis

This project uses PHPStan for static analysis. Before submitting your changes, run:

```bash
task stan
```

## Documentation

If you make changes that require documentation updates, please include those updates in your pull request.

## Questions?

If you have any questions, feel free to open an issue or contact the maintainers.

Thank you for contributing!