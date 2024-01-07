<template>
  <k-field v-bind="$props" class="k-table-field">
    <template v-if="!disabled" #options>
			<k-button
        :disabled="columns.length >= maxColumns"
				text="Column"
				icon="add"
				variant="filled"
				size="xs"
				@click="addColumn()"
			/>
		</template>

    <div :aria-disabled="disabled" class="k-table">
      <table :data-disabled="disabled" :data-indexed="hasIndexColumn">
        <!-- Header Row -->
        <thead>
          <k-draggable
            :list="values"
            :options="dragOptions"
            :handle="true"
            element="tr"
            @end="onColumnDrag"
          >
            <th v-if="hasIndexColumn" class="k-table-index-column">#</th>

            <!-- Columns -->
            <th
              v-for="(column, columnIndex) in columns"
              :key="columnIndex + '-header'"
              :data-sortable="!disabled && sortable"
              class="k-table-column"
            >
              <div class="k-bar">
                <k-sort-handle v-if="!disabled && sortable" class="k-table-sort-handle" />
                <k-text-input
                  v-model="columns[columnIndex]"
                  @input="updateTable()"
                  :placeholder="`Column ${columnIndex + 1}`"
                  type="text"
                />
                <!-- Column Options -->
                <k-options-dropdown v-if="!disabled"
                  :options="[
                    {
							        click: () => insertColumn(columnIndex, 'before'),
                      disabled: columns.length >= maxColumns,
							        icon: 'angle-left',
							        text: $t('insert.before')
						        },
                    {
							        click: () => insertColumn(columnIndex, 'after'),
                      disabled: columns.length >= maxColumns,
							        icon: 'angle-right',
							        text: $t('insert.after')
						        },
						        {
							        click: () => duplicateColumn(columnIndex),
                      disabled: columns.length >= maxColumns,
							        icon: 'copy',
							        text: $t('duplicate')
						        },
						        {
							        click: () => removeColumn(columnIndex),
                      disabled: columns.length <= minColumns,
							        icon: 'trash',
							        text: $t('delete')
						        }
					        ]"
								/>
              </div>
            </th>

            <th v-if="!disabled" class="k-table-options-column"></th>
          </k-draggable>
        </thead>

        <!-- Rows -->
        <k-draggable
          :list="values"
          :options="dragOptions"
          :handle="true"
          element="tbody"
          @end="onRowDrag"
        >
          <!-- Empty -->
				  <tr v-if="rows.length === 0">
					  <td :colspan="colspan" class="k-table-empty">
						  {{ empty ?? 'No rows yet' }}
					  </td>
				  </tr>

          <template v-else>
            <tr v-for="(row, rowIndex) in rows" :key="rowIndex">
              <!-- Index & drag handle -->
              <td v-if="hasIndexColumn" :data-sortable="!disabled && sortable && rows.length > 1" class="k-table-index-column">
								<div class="k-table-index" v-text="index + rowIndex" />
                <k-sort-handle v-if="!disabled && sortable && rows.length > 1" class="k-table-sort-handle" />
              </td>

              <!-- Cell -->
              <td v-for="(column, columnIndex) in row" :key="columnIndex" class="k-table-column">
                <k-text-input
                  v-model="row[columnIndex]"
                  @input="updateTable()"
                  type="text"
                />
              </td>

              <!-- Row Options -->
              <td v-if="!disabled" class="k-table-options-column">
                <k-options-dropdown
                  :options="[
                    {
							        click: () => insertRow(rowIndex, 'before'),
							        icon: 'angle-up',
							        text: $t('insert.before')
						        },
                    {
							        click: () => insertRow(rowIndex, 'after'),
							        icon: 'angle-down',
							        text: $t('insert.after')
						        },
						        {
							        click: () => duplicateRow(rowIndex),
							        icon: 'copy',
							        text: $t('duplicate')
						        },
						        {
							        click: () => removeRow(rowIndex),
							        icon: 'trash',
							        text: $t('delete')
						        }
					        ]"
								/>
              </td>
            </tr>
          </template>
        </k-draggable>
      </table>
    </div>

    <!-- Footer -->
    <footer v-if="!disabled" data-align="center" class="k-bar">
      <k-button
        text="Row"
        icon="add"
        size="xs"
        variant="filled"
        @click="addRow()"
      />
    </footer>
  </k-field>
</template>

<script>
export default {
  props: {
    label: String,
    type: String,
    help: String,
    empty: String,
    disabled: Boolean,
    required: Boolean,
    value: [String, Array],

    index: {
			type: [Number, Boolean],
			default: 1
		},
    sortable: {
      type: Boolean,
      default: true
    },
    minColumns: {
      type: Number,
      default: 2
    },
    maxColumns: {
      type: Number,
      default: 5
    }
  },
  computed: {
    values() {
      return [...this.tableData];
    },
    columns() {
      return this.tableData[0];
    },
    rows() {
      return this.tableData.slice(1);
    },
    colspan() {
			let span = this.columns.length + 1;

			if (this.hasIndexColumn) {
				span++;
			}

			return span;
		},
    dragOptions() {
			return {
        disabled: !this.sortable,
				fallbackClass: "k-table-row-fallback",
				ghostClass: "k-table-row-ghost"
			};
		},
    hasIndexColumn() {
			return this.sortable || this.index !== false;
		},
    tableData() {
      const clearValue = (value) => value.trim().replace(/^- /, "").replace(/^[\"\'](.*)[\"\']$/g, "$1");
      const isRowBreak = (value) => value === '- ';

      let array = typeof this.value === 'string'
        ? this.value.split('\n')
          .reduce((row, value) => (
            isRowBreak(value)
              ? row.push([])
              : row[row.length - 1].push(clearValue(value)),
            row
          ), [])
          .filter(row => row.length > 0)
          .map(row => [...row])
        : this.value;

      array ||= Array.from({ length: 1 }, () => Array(this.minColumns).fill(''));

      return array;
    },
  },
  methods: {
    moveArray(array, oldIndex, newIndex) {
      const [movedIndex] = array.splice(oldIndex, 1);
      array.splice(newIndex, 0, movedIndex);
    },
    moveColumn(oldIndex, newIndex) {
      this.moveArray(this.columns, oldIndex, newIndex);
      this.rows.forEach((column) => this.moveArray(column, oldIndex, newIndex));
      this.updateTable();
    },
    moveRow(oldIndex, newIndex) {
      this.moveArray(this.tableData, oldIndex, newIndex);
      this.updateTable();
    },
    onColumnDrag(event) {
      this.moveColumn(event.oldIndex - 1, event.newIndex - 1);
    },
    onRowDrag(event) {
      this.moveRow(event.oldIndex + 1, event.newIndex + 1);
    },
    addRow() {
      this.tableData.push(Array(this.columns.length).fill(''));
      this.updateTable();
    },
    addColumn() {
      this.tableData.forEach((column) => column.push(""));
      this.updateTable();
    },
    removeColumn(columnIndex) {
      this.tableData.forEach((column) => column.splice(columnIndex, 1));
      this.updateTable();
    },
    removeRow(rowIndex) {
      this.tableData.splice(rowIndex + 1, 1);
      this.updateTable();
    },
    insertColumn(columnIndex, insert = "before") {
      const insertIndex = insert === "before" ? columnIndex : columnIndex + 1;
      this.columns.splice(insertIndex, 0, '');
      this.rows.forEach((row) => row.splice(insertIndex, 0, ''));
      this.updateTable();
    },
    insertRow(rowIndex, insert = "before") {
      const insertIndex = insert === "before" ? rowIndex + 1 : rowIndex + 2;
      this.tableData.splice(insertIndex, 0, Array(this.columns.length).fill(''));
      this.updateTable();
    },
    duplicateColumn(columnIndex) {
      this.columns.splice(columnIndex + 1, 0, this.columns[columnIndex]);
      this.rows.forEach((row) => row.splice(columnIndex + 1, 0, row[columnIndex]));
      this.updateTable();
    },
    duplicateRow(rowIndex) {
      this.tableData.splice(rowIndex + 1, 0, [...this.tableData[rowIndex + 1]]);
      this.updateTable();
    },
    updateTable() {
      this.$emit('input', this.tableData);
    }
  }
};
</script>
