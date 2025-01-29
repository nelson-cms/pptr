'use strict';

class Generator
{
	/**
	 * @param args Object
	 * @param {String} args.input
	 * @param {String} args.inputMode
	 * @param {String} args.output
	 * @param {Boolean} args.pdf
	 * @param {Boolean} args.image
	 * @param {Boolean} args.sandbox
	 * @param {String} args.httpUser
	 * @param {String} args.httpPass
	 * @param {Number} args.viewportWidth
	 * @param {Number} args.viewportHeight
	 * @param {String} args.pageFormat
	 * @param {Number} args.pageWidth
	 * @param {Number} args.pageHeight
	 * @param {Boolean} args.landscape
	 * @param {Number} args.timeout
	 */
	constructor(args)
	{
		this.args = args;
		this.outputPdf = false;
		this.outputImage = false;
		this.browserArgs = [
			'--disable-gpu',
			'--hide-scrollbars',
		];

		// console.log(args);
		// return;

		this.#checkArg(this.args.input);
		this.#checkArg(this.args.inputMode);
		this.#checkArg(this.args.output);

		this.httpAuth = this.#getHttpAuthParams();
		this.viewport = this.#getViewport();

		// Process outputMode, this is not either/or, it can be both
		if (this.#isset(this.args.pdf)) {
			this.outputPdf = true;
			this.pageParameters = {
				path: this.args.output + '.pdf',
				margin: 0,
				printBackground: true,
				preferCSSPageSize: true,
				timeout: this.args.timeout,
			};
			this.#setPageFormat();
			this.#setPageDimensions();
			this.#setLandscape();
		}

		if (this.#isset(this.args.image)) {
			this.outputImage = true;
		}

		if (this.#isset(this.args.sandbox) && this.args.sandbox === false) {
			this.browserArgs.push('--no-sandbox');
		}
	}


	#isset(variable)
	{
		return typeof variable !== 'undefined';
	}


	#checkArg(arg, message = '')
	{
		if (!this.#isset(arg)) {
			throw Error('Argument ' + arg + ' not set. ' + message);
		}
	}


	#getHttpAuthParams()
	{
		if (this.#isset(this.args.httpUser) && this.#isset(this.args.httpPass)) {
			return {
				username: this.args.httpUser,
				password: this.args.httpPass,
			};
		}
	}


	#getViewport()
	{
		if (this.#isset(this.args.viewportWidth) && this.#isset(this.args.viewportHeight)) {
			return {
				width: this.args.viewportWidth,
				height: this.args.viewportHeight,
			};
		}
	}


	#setPageFormat()
	{
		if (this.#isset(this.args.pageFormat)) {
			this.pageParameters['format'] = this.args.pageFormat;
		}
	}


	#setPageDimensions()
	{
		if (
			this.#isset(this.args.pageWidth)
			&&
			this.#isset(this.args.pageHeight)
		) {
			this.pageParameters['width'] = this.args.pageWidth + 'mm';
			this.pageParameters['height'] = this.args.pageHeight + 'mm';
		}
	}


	#setLandscape()
	{
		if (this.#isset(this.args.landscape)) {
			this.pageParameters['landscape'] = true;
		}
	}


	/**
	 * @param {Page} page
	 */
	async #setHttpAuth(page)
	{
		if (this.httpAuth) {
			if (this.args.inputMode === 'file') {
				const auth = Buffer.from(`${this.httpAuth.username}:${this.httpAuth.password}`).toString('base64');
				await page.setExtraHTTPHeaders({
					'Authorization': `Basic ${auth}`
				});
			} else if (this.args.inputMode === 'url') {
				await page.authenticate(this.httpAuth);
			}
		}
	}


	async generate()
	{
		const browser = await puppeteer.launch({
			headless: true,
			args: this.browserArgs,
		});

		const page = await browser.newPage();

		await this.#setHttpAuth(page);
		await page.setDefaultNavigationTimeout(0);
		await page.setDefaultTimeout(this.args.timeout);

		if (this.viewport) {
			await page.setViewport(this.viewport);
		}

		const options = {
			waitUntil: ['load', 'networkidle0', 'domcontentloaded'],
		};

		// Set url/content
		switch (this.args.inputMode) {
			case 'file':
				await page.setContent(fs.readFileSync(this.args.input, 'utf8'), options);
				break;

			case 'url':
				await page.goto(this.args.input, options);
				break;

			default:
				throw Error('Invalid inputMode: ' + this.args.inputMode);
		}

		if (this.outputImage) {
			await page.screenshot({
				path: this.args.output + '.png',
				fullPage: true,
			});
		}

		if (this.outputPdf) {
			await page.emulateMediaType('print');
			await page.pdf(this.pageParameters);
		}

		await browser.close();
	}
}

const puppeteer = require('puppeteer');
const argv = require('minimist')(process.argv.slice(2));
const fs = require('fs');
const generator = new Generator(argv);

generator.generate().catch((e) => {
	console.log(e);
});
