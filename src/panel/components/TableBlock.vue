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
                v-if="hasHeaders"
                v-model="columns[columnIndex]"
                type="text"
                :spellcheck="false"
                :placeholder="`${$t('field.table.column')} ${columnIndex + 1}`"
                @input="update"
              />
              <k-text v-else>
                {{ $t('field.table.column') }} {{ columnIndex + 1 }}
              </k-text>
            </th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="(row, rowIndex) in rows" :key="rowIndex">
            <td
              v-for="(column, columnIndex) in row"
              :key="rowIndex + '-' + columnIndex"
              :data-align="hasAlignment"
              data-mobile="true"
              class="k-table-column"
            >
              <k-writer-input
                v-model="row[columnIndex]"
                :inline="true"
                :nodes="false"
                :marks="tableField.marks"
                :spellcheck="false"
                @input="update()"
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
    table() {
      return this.content.table ?? Array.from(
        { length: this.hasHeaders ? 2 : 1 },
        () => Array.from({ length: 2 }).fill(""),
      );
    },
    columns() {
      const firstRow = this.table[0] ?? [];

      return this.hasHeaders
        ? firstRow
        : Array.from({
            length: firstRow.length || this.tableField.minColumns || 2,
          }).fill("");
    },
    rows() {
      return this.hasHeaders ? this.table.slice(1) : this.table;
    },
    tableField() {
      return this.field("table");
    },
    hasHeaders() {
      return this.tableField.headers !== false;
    },
    hasAlignment() {
      return this.tableField.align;
    },
  },
};
</script>
