## Test Setup

The tests require a local API server running on `localhost:8888` that serves the files in `{base_dir}/tests/tend-endpoint/`.  You can use PHP's built-in server to accomplish this with the following command:

```
php -S localhost:8888 -t tests/test-endpoint
````

It also requires the existence of the CLI version of `curl` and assumes that the executable is can be found in the current PATH environment.