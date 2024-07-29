# Override Steps Extension

Allows ignoring duplicated steps within suite contexts by overriding them based on the contexts order.

## Usage

1. Install it:

    ```bash
    $ composer require no-response-mate/override-steps --dev
    ```

2. Enable it in your Behat configuration:

    ```yml
    # behat.yml
    default:
        # ...
        extensions:
            NoResponseMate\OverrideStepsExtension: ~
    ```
