<template>
  <k-block-figure
    :caption="content.caption"
    :caption-marks="captionMarks"
    :disabled="disabled"
    @open="open"
    @update="update"
  >
    <div class="k-table">
      <table>
        <thead>
          <tr>
            <th
              v-for="(column, columnIndex) in columns"
              :key="columnIndex + 'header'"
              :data-align="hasAlignment"
              data-mobile="true"
              class="k-table-column"
            >
              <k-text-input
                type="text"
                :spellcheck="false"
                :placeholder="`${$t('field.table.column')} ${columnIndex + 1}`"
                :value="column"
                @input="updateCell(0, columnIndex, $event)"
              />
            </th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="(row, rowIndex) in rows" :key="rowIndex">
            <td
              v-for="(cell, columnIndex) in row"
              :key="rowIndex + '-' + columnIndex"
              :data-align="hasAlignment"
              data-mobile="true"
              class="k-table-column"
            >
              <k-writer-input
                :inline="true"
                :nodes="false"
                :marks="tableField.marks"
                :spellcheck="false"
                :value="cell"
                @input="updateCell(rowIndex + 1, columnIndex, $event)"
              />
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </k-block-figure>
</template>

<script>
export default {
  computed: {
    captionMarks() {
      return this.field("caption", { marks: true }).marks;
    },
    columns() {
      return this.table[0];
    },
    hasAlignment() {
      return this.tableField.align;
    },
    rows() {
      return this.table.slice(1);
    },
    table() {
      const minColumns = this.tableField.minColumns ?? 2;
      const table = Array.isArray(this.content.table)
        ? this.content.table.filter(row => Array.isArray(row))
        : [];

      if (table.length === 0) {
        return Array.from({ length: 2 }, () => Array.from({ length: minColumns }).fill(""));
      }

      const width = Math.max(minColumns, ...table.map(row => row.length));

      return table.map(row => [
        ...row.map(cell => cell ?? ""),
        ...Array.from({ length: width - row.length }).fill(""),
      ]);
    },
    tableField() {
      return this.field("table", {});
    },
  },
  methods: {
    updateCell(rowIndex, columnIndex, value) {
      const table = this.table.map(row => [...row]);
      table[rowIndex][columnIndex] = value;
      this.update({ table });
    },
  },
};
</script>
