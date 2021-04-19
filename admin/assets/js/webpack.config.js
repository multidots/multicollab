const path = require( 'path' );
const UglifyJsPlugin = require( 'uglifyjs-webpack-plugin' );

// DIR Paths
const JS_DIR = path.resolve( __dirname, 'src' );
const BUILD_DIR = path.resolve( __dirname, 'dist' );

// Entry Scripts
const entry = {
    block: `${JS_DIR}/block.js`,
    activityCentre: `${JS_DIR}/activityCentre.js`,
}

// Output Scripts
const output = {
    path: BUILD_DIR,
    filename: '[name].build.min.js'
}

// Rules
const rules = [
    {
        test: /\.js$/,
        include: [ JS_DIR ],
        exclude: /node_modules/,
        use: 'babel-loader'
    }
]

// Plugins
const plugins = ( argv ) => {}

// Exporting Modules
module.exports = ( env, argv ) => ({

    entry: entry,
    output: output,
    devtool: 'source-map',
    module: {
        rules: rules,
    },
    optimization: {
        minimizer: [
            new UglifyJsPlugin({
                cache: false,
                parallel: true,
                sourceMap: false
            })
        ]
    }

})