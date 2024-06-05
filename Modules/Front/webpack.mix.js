const mix = require("laravel-mix")
let SVGSpritemapPlugin = require("svg-spritemap-webpack-plugin");


mix
.js("Resources/assets/src/js/main.js", "js")
.sass("Resources/assets/src/scss/main.scss", "css")
.setPublicPath("../../public/assets/front")
.options({
  postCss: [require("tailwindcss")],
})
  .browserSync({
    server: "./",
    files: ["./src", "./dist"],
  });

mix
  .options({
    processCssUrls: false,
  })
  .webpackConfig({
    plugins: [
      new SVGSpritemapPlugin("Resources/assets/src/svg/*.svg", {
        output: {
          filename: "assets/svg/icon.svg",
          svg4everybody: true,
          svgo: {
            removeTitle: true,
            removeStyleElement: true,
            cleanupNumericValue: true,
          },
          chunk: {
            keep: true,
          },
        },
      }),
    ],
  });
