import antfu from "@antfu/eslint-config";

export default antfu(
  {
    stylistic: {
      indent: 2,
      semi: true,
      quotes: "double",
    },
    formatters: {
      css: true,
    },
    vue: {
      overrides: {
        "vue/attributes-order": "error",
        "vue/block-order": ["error", {
          order: ["template", "script", "style"],
        }],
        "vue/component-definition-name-casing": "off",
        "vue/multi-word-component-names": "off",
        "vue/prefer-template": "off",
        "vue/require-default-prop": "off",
      },
      // https://github.com/antfu/eslint-config/issues/367
      sfcBlocks: {
        blocks: {
          styles: false,
        },
      },
      vueVersion: 2,
    },
    ignores: ["**/vendor/**", "index.css", "index.js"],
  },
  {
    rules: {
      "style/brace-style": ["warn", "1tbs", { allowSingleLine: true }],
      "regexp/no-super-linear-backtracking": "off",
    },
  },
);
