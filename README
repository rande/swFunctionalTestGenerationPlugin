# Functional Test Generation

This plugin contains a specific filter which just records user's interactions in a symfony Functional Test format.

So mainly you navigate accross your application, submit your forms and click on links, and boom ... you have a functional test *almost* ready to use.

Please read the original blog post : [swFilterFunctionalTest](http://rabaix.net/en/articles/2009/01/27/functional-test-generation-with-symfony-1-2 "Functional Test Generation with symfony 1.2")

## Installation

* Install swFunctionalTestGenerationPlugin

  - via svn : http://svn.symfony-project.com/plugins/swFunctionalTestGenerationPlugin/tags/VERSION_1_2_0
  - via pear : ./symfony plugin:install --release=1.2.0 swFunctionalTestGenerationPlugin

* Clear your cache

  - ./symfony cc

* Edit the filters.yml file and add these configuration lines after the rendering filter

        [yml]
        functional_test:
          class: swFilterFunctionalTest

* Make sure the debug panel is enabled


## Usage

* Enable the functional test in the debug bar

* Perform a scenario on your project

* once done, copy-paste the generated code into a test file