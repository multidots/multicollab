module.exports = {
    entry: './src/block.js',
    output: {
        path: __dirname,
        filename: 'src/block.build.min.js',
    },
    module: {
        loaders: [
            {
                test: /.js$/,
                loader: 'babel-loader',
                exclude: /node_modules/,
            },
            {
                test: /\.css$/i,
                use: ['style-loader', 'css-loader'],
            },
        ],

    },
};