const mix = require("laravel-mix")
require("dotenv").config({ path: "../../.env" });
const appUrl = process.env.APP_URL || 'http://localhost:8000';
let SVGSpritemapPlugin = require("svg-spritemap-webpack-plugin");


mix
.js("resources/assets/src/js/main.js", "js")
.sass("resources/assets/src/scss/main.scss", "css")
.setPublicPath("../../public/assets/front")
.options({
  postCss: [require("tailwindcss")],
})
  .browserSync({
      proxy: appUrl.replace(/^https?:\/\//, ''),
      files: [
          "./Modules/Front/Resources/**/*", // فایل‌های داخل ماژول
          "resources/views/**/*.blade.php",
          "public/modules/front/**/*"
      ],
      open: false,
      notify: false,
  });

mix
  .options({
    processCssUrls: false,
  })
  .webpackConfig({
    plugins: [
      new SVGSpritemapPlugin("resources/assets/src/svg/*.svg", {
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
