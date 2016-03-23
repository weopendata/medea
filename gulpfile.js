var path = require('path');
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
	mix.webpack([
			'finds-list.js',
			'finds-detail.js',
			'finds-create.js',
			'home.js',
		], {
			module: {
				loaders: [
					{ test: /\.css$/, loader: 'style!css' },
					{ test: /\.vue$/, loader: 'vue' },
					{ test: /\.scss$/, loaders: ['style', 'css', 'sass', 'scss'] },
					{ test: /\.js$/, loader: 'babel-loader', include:  [
						path.resolve(__dirname, 'resources/assets'),
						path.resolve(__dirname, 'node_modules/vue-google-maps')
					]}
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
