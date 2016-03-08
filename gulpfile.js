var elixir = require('laravel-elixir');
require('laravel-elixir-webpack-ex');
require('laravel-elixir-livereload');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {
	mix.sass('app.scss', 'public/css')
	mix.webpack({
			findslist: 'findslist.js'
		}, {
			module: {
				loaders: [
					{ test: /\.css$/, loader: 'style!css' },
					{ test: /\.vue$/, loader: 'vue' },
					{ test: /\.scss$/, loaders: ['style', 'css', 'sass', 'scss'] },
					{ test: /\.js$/, exclude: /node_modules/, loader: 'babel-loader', exclude: /node_modules/ },
				],
			},
			resolve: {
				extensions: ['', '.js', '.vue']
			},
		},
		'./public/js', 'resources/assets/js'
	)
	mix.livereload()
});
