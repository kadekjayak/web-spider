# Web Spider
This is simple website spider class written in PHP, it can be used to extract links from a website recursively.

## Installation
Just download this class and include on your project.
or by using composer
	
	composer require kadekjayak/web-spider

##Requirements
* PHP curl

## Example
the basic example to get all links from a website

	use Kadekjayak\WebSpider;
	$Spider = new WebSpider();
	$Links = $Spider->scan('http://example.com', $depth = 2);
	print_r($Links);

