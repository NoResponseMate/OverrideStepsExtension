Feature: Overriding steps with multiple contexts
    In order to not care about duplicated steps
    As a Behat User
    I want for duplicated steps to be overridden by the last context in a suite

    Background:
        Given a Behat configuration containing:
        """
        default:
            extensions:
                NoResponseMate\OverrideStepsExtension: ~
            suites:
                default:
                    contexts:
                        - FirstContext
                        - SecondContext
        """
        And a context file "features/bootstrap/FirstContext.php" containing:
        """
        <?php

        use Behat\Behat\Context\Context;

        class FirstContext implements Context
        {
            /**
             * @When I get the property
             */
            public function iGetTheProperty()
            {
                printf('property value: first');
            }
        }
        """
        And a context file "features/bootstrap/SecondContext.php" containing:
        """
        <?php

        use Behat\Behat\Context\Context;

        class SecondContext implements Context
        {
            /**
             * @When I get the property
             */
            public function iGetTheProperty()
            {
                printf('property value: second');
            }
        }
        """
        And a feature file containing:
        """
        Feature: Overriding steps with multiple contexts
            Scenario: Subsequent contexts steps override previous ones
                When I get the property
        """

    Scenario: Duplicated steps are overridden by the last context
        When I run Behat
        Then it should pass
        And its output should contain "property value: second"

    Scenario: Overriding order depends on the order of contexts
        Given a Behat configuration containing:
        """
        default:
            extensions:
                NoResponseMate\OverrideStepsExtension: ~
            suites:
                default:
                    contexts:
                        - SecondContext
                        - FirstContext
        """
        When I run Behat
        Then it should pass
        And its output should contain "property value: first"

    Scenario: With duplications only last context step are executed
        Given a context file "features/bootstrap/ThirdContext.php" containing:
        """
        <?php

        use Behat\Behat\Context\Context;

        class ThirdContext implements Context
        {
            /**
             * @When I get the property
             */
            public function iGetTheProperty()
            {
                printf('property value: third');
            }
        }
        """
        And a context file "features/bootstrap/FourthContext.php" containing:
        """
        <?php

        use Behat\Behat\Context\Context;

        class FourthContext implements Context
        {
            /**
             * @When I get the property
             */
            public function iGetTheProperty()
            {
                printf('property value: fourth');
            }
        }
        """
        Given a Behat configuration containing:
        """
        default:
            extensions:
                NoResponseMate\OverrideStepsExtension: ~
            suites:
                default:
                    contexts:
                        - FirstContext
                        - SecondContext
                        - ThirdContext
                        - FourthContext
        """
        When I run Behat
        Then it should pass
        And its output should contain "property value: fourth"
