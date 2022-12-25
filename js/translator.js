/**
 * @author Hidehito NOZAWA
 * @author schnabear
 * @copyright 2010 Hidehito NOZAWA
 * @licence GNU GPL v3
 */

var Translator = function()
{
	this.catalog = {};
	
	this.translate = function(message)
	{
		try
		{
			if ( Translator.catalog[message] )
			{
				return Translator.catalog[message];
			}
		}
		catch(e)
		{
		}

		return message;
	};
	
	return this;
}

translator = new Translator();

function t(message)
{
	var message = translator.translate(message);

	if ( arguments.length == 1 ) return message;

	// var openTag = /\{/;
	// var closeTag = /\}/;

	for ( var i = 1; i < arguments.length; i++ )
	{
		// var pattern = new RegExp(openTag.source + i + closeTag.source, 'g');
		var pattern = new RegExp("\\{"+i+"\\}", 'g');
		message = message.replace(pattern, arguments[i]);
	}

	return message;
}
