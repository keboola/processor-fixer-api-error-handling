# Processor for handling errors from Fixer API

> Processor is meant to be used after extractor for Fixer API. Fixer API returns status code 200 even if error and actual error code and message is in response body. This processor should fail with that message if error in response body is found.

# Usage

See https://developers.keboola.com/extend/component/processors/

## Development
 
Clone this repository and init the workspace with following command:

```
git clone https://github.com/keboola/processor-fixer-api-error-handling.git
cd processor-fixer-api-error-handling
docker-compose build
docker-compose run --rm dev composer install --no-scripts
```

Run the test suite using this command:

```
docker-compose run --rm dev composer tests
```
 
# Integration

For information about deployment and integration with KBC, please refer to the [deployment section of developers documentation](https://developers.keboola.com/extend/component/deployment/) 
