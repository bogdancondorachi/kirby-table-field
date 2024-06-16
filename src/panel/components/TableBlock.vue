<template>
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
              v-model="columns[columnIndex]"
              type="text"
              :spellcheck="false"
              :placeholder="`${$t('field.table.column')} ${columnIndex + 1}`"
              @input="update()"
            />
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
</template>

<script>
export default {
	computed: {
    table() {
      return this.content.table ?? Array.from({ length: 2 }, () => Array(2).fill(''));
    },
		columns() {
			return this.table[0];
		},
		rows() {
			return this.table.slice(1);
		},
    tableField() {
      return this.field('table')
    },
    hasAlignment() {
      return this.tableField.align;
    },
	}
};
</script>
