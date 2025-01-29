# Puppeteer

## Install

1. `composer require nelson-cms/pptr`.
2. Navigate to `%vendorDir%/nelson-cms/pptr` and run `npm install` - be sure to do this on the target machine!

## Configuration

1. Enable the extension:
	``` neon
	extensions:
		pptr: NelsonCms\Pptr\DI\PptrExtension
	```
2. Configuration:

	``` neon
	pptr:
 		connection: @ssh2.connection
		tempDir: '%tempDir%/puppeteer/'
		timeout: 30_000 # miliseconds
		sandbox: false
 		outline: false # Generate document outline
		nodeCommand: 'node' # in case multiple versions are installed or for additional arguments
 		scriptPath: '%vendorDir%/nelson-cms/pptr/src/assets/generator.js'
 		httpUser: # http auth
		httPass: # http auth
	```

	These are the default values.

## Sandbox

Using sandbox is highly encouraged (by Puppeteer team). It has to be set up on the target machine.

- https://github.com/GoogleChrome/puppeteer/blob/master/docs/troubleshooting.md#alternative-setup-setuid-sandbox

## Usage

Most basic usage:

``` php
$html = '<body style="color: #fff; background: rebeccapurple"><h1>Puppeteer test</h1><p>Some text paragraph</p>';
$html .= '<p>' . date(DateTime::ISO8601) . '</p>';
$html .= '</body>';

/** @var Generator $generator */
$generator = $this->generatorFactory->create();
$output = $generator->generateFromHtml($html, OutputMode::BOTH);
```

Note: The HTML is not directly passed to the node process. Instead, it is first saved to a temp file and then read by node. This is done intentionally for the generator to deal with large HTML payloads. `nesk/puphpeteer` suffers from this problem, as the data is sent via JSON (AFAIK) and crashes on large HTML, otherwise it works just fine and is a great tool.

There are currently three output modes:

- `OutputMode::PDF`
- `OutputMode::IMG`
- `OutputMode::BOTH`

These are self-explanatory.

The generator also supports generating from URL:

``` php
/** @var \NelsonCms\Pptr\VO\Result $result */
$result = $generator->generateFromUrl(new UrlScript('https://www.google.com'), OutputMode::BOTH);
```

``` php
$result->getCommand();

// array(7) {
// 	[0]=>	string(4) "node"
// 	[1]=>	string(91) "/xyz/puppeteer/src/assets/generator.js"
// 	[2]=>	string(16) "--inputMode=file"
// 	[3]=>	string(159) "--input=/xyz/puppeteer/app/../temp/puppeteer/1562653154_-_58f8da81a3c0c3399838891fe88d0db7.html"
// 	[4]=>	string(5) "--pdf"
// 	[5]=>	string(7) "--image"
// 	[6]=>	string(155) "--output=/xyz/puppeteer/app/../temp/puppeteer/1562653154_-_58f8da81a3c0c3399838891fe88d0db7"
// }

$result->getPdf(); // string(150) "/xyz/puppeteer/app/../temp/puppeteer/1562653154_-_58f8da81a3c0c3399838891fe88d0db7.pdf"
$result->getImage(); // string(150) "/xyz/puppeteer/app/../temp/puppeteer/1562653154_-_58f8da81a3c0c3399838891fe88d0db7.png"
$result->getConsole(); // string(0) ""
```

Legend:

- `pdf`/`image` are dependent on the mode used.
- `command` - raw command passed from PHP to NODE.js via Symfony/Process.
- `console` - raw output from NODE (via `console.log`). Should be empty in most cases.



