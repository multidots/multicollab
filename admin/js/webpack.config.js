module.exports = {
    entry: './blockJS/block.js',
    output: {
        path: __dirname,
        filename: 'blockJS/block.build.min.js',
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
    externals: {
        'react': 'React',
        'react-dom': 'ReactDOM',
    },
};
