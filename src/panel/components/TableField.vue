<template>
  <k-field v-bind="$props" class="k-table-field">
    <template v-if="!disabled" #options>
      <k-button-group layout="collapsed">
			  <k-button
          :disabled="columns.length >= maxColumns"
				  :text="$t('field.table.column')"
				  icon="add"
				  variant="filled"
				  size="xs"
				  @click="addColumn()"
			  />
        <k-button
					icon="dots"
					size="xs"
					variant="filled"
					@click="$refs.options.toggle()"
				/>
				<k-dropdown-content
					ref="options"
					:options="options"
					align-x="end"
				/>
      </k-button-group>
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

            <!-- Header Column -->
            <th
              v-for="(column, columnIndex) in columns"
              :key="columnIndex + '-header'"
              :data-sortable="!disabled && sortable"
              class="k-table-column k-table-header"
            >
              <k-bar>
                <k-sort-handle v-if="!disabled && sortable" class="k-table-sort-handle" />
                <k-text-input
                  v-if="hasHeaders"
                  v-model="columns[columnIndex]"
                  @input="updateTable()"
                  :placeholder="`${$t('field.table.column')} ${columnIndex + 1}`"
                  type="text"
                />
                <k-text v-else>{{ $t('field.table.column') }} {{ columnIndex + 1 }}</k-text>
                <!-- Options -->
                <k-options-dropdown v-if="!disabled"
                  :options="columnOptions"
                  @option="columnOption($event, columnIndex)"
								/>
              </k-bar>
            </th>

            <th v-if="!disabled && rows.length !== 0" class="k-table-options-column"></th>
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
						  {{ empty ?? $t('field.table.empty.rows') }}
					  </td>
				  </tr>

          <template v-else>
            <tr v-for="(row, rowIndex) in rows" :key="rowIndex">
              <!-- Index & drag handle -->
              <td v-if="hasIndexColumn" :data-sortable="!disabled && sortable && rows.length > 1" class="k-table-index-column">
								<div class="k-table-index" v-text="index + rowIndex" />
                <k-sort-handle v-if="!disabled && sortable && rows.length > 1" class="k-table-sort-handle" />
              </td>

              <!-- Column -->
              <td v-for="(column, columnIndex) in row" :key="columnIndex" class="k-table-column k-table-cell">
                <k-text-input
                  v-model="row[columnIndex]"
                  @input="updateTable()"
                  type="text"
                />
              </td>

              <!-- Options -->
              <td v-if="!disabled" class="k-table-options-column">
                <k-options-dropdown
                  :options="rowOptions"
                  @option="rowOption($event, rowIndex)"
								/>
              </td>
            </tr>
          </template>
        </k-draggable>
      </table>
    </div>

    <!-- Footer -->
    <footer v-if="!disabled">
      <k-button
        :text="$t('field.table.row')"
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
    disabled: Boolean,
    empty: String,
    help: String,
    label: String,
    required: Boolean,
    type: String,

    duplicate: {
      type: Boolean,
      default: true
    },
    headers: {
      type: Boolean,
      default: true
    },
    index: {
			type: [Number, Boolean],
			default: 1
		},
    minColumns: {
      type: Number,
      default: 2
    },
    maxColumns: {
      type: Number,
      default: 5
    },
    sortable: {
      type: Boolean,
      default: true
    },
    value: [String, Array]
  },
  computed: {
    values() {
      return [...this.tableData];
    },
    columns() {
      return this.hasHeaders
        ? this.tableData[0]
        : this.tableData[0].length > 0
          ? Array(this.tableData[0].length).fill('')
          : Array(this.minColumns).fill('');
    },
    rows() {
      return this.hasHeaders ? this.tableData.slice(1) : this.tableData;
    },
    colspan() {
			let span = this.columns.length;

			if (this.hasIndexColumn) {
				span++;
			}

			return span;
		},
    hasHeaders() {
      return this.headers;
    },
    hasIndexColumn() {
			return this.sortable || this.index !== false;
		},
    tableData() {
      const clearValue = (value) => value.trim().replace(/^- /, "").replace(/^[\"\'](.*)[\"\']$/g, "$1");
      const isRowBreak = (value) => value === '- ';
      const rowCount = this.hasHeaders ? 2 : 1;

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

      array ||= Array.from({ length: rowCount }, () => Array(this.minColumns).fill(''));

      return array;
    },
    dragOptions() {
			return {
        disabled: !this.sortable,
				fallbackClass: "k-table-row-fallback",
				ghostClass: "k-table-row-ghost"
			};
		},
    options() {
      return [
				{
					click: () => this.removeAll(),
          disabled: this.rows.length === 0 && this.columns.length <= this.minColumns,
					icon: 'trash',
					text: this.$t('delete.all')
				}
      ];
    },
    columnOptions() {
      return [
        {
          disabled: this.columns.length >= this.maxColumns,
          icon: "angle-left",
          text: this.$t("insert.before"),
          click: "insertBefore"
        },
        {
          disabled: this.columns.length >= this.maxColumns,
          icon: "angle-right",
          text: this.$t("insert.after"),
          click: "insertAfter"
        },
        "-",
        {
          disabled: !this.duplicate || this.columns.length >= this.maxColumns,
          icon: "copy",
          text: this.$t("duplicate"),
          click: "duplicate"
        },
        "-",
        {
          disabled: this.columns.length <= this.minColumns,
          icon: "trash",
          text: this.$t("delete"),
          click: "remove"
        }
      ];
    },
    rowOptions() {
      return [
        {
          icon: "angle-up",
          text: this.$t("insert.before"),
          click: "insertBefore"
        },
        {
          icon: "angle-down",
          text: this.$t("insert.after"),
          click: "insertAfter"
        },
        "-",
        {
          disabled: !this.duplicate,
          icon: "copy",
          text: this.$t("duplicate"),
          click: "duplicate"
        },
        "-",
        {
          disabled: this.rows.length <= 1,
          icon: "trash",
          text: this.$t("delete"),
          click: "remove"
        }
      ];
    },
  },
  methods: {
    columnOption(option, columnIndex) {
      switch (option) {
        case "insertBefore":
          this.insertColumn(columnIndex, 'before')
          break;
        case "insertAfter":
          this.insertColumn(columnIndex, 'after')
          break;
        case "duplicate":
          this.duplicateColumn(columnIndex)
          break;
        case "remove":
          this.removeColumn(columnIndex);
          break;
      }
    },
    rowOption(option, rowIndex) {
      switch (option) {
        case "insertBefore":
          this.insertRow(rowIndex, 'before')
          break;
        case "insertAfter":
          this.insertRow(rowIndex, 'after')
          break;
        case "duplicate":
          this.duplicateRow(rowIndex)
          break;
        case "remove":
          this.removeRow(rowIndex);
          break;
      }
    },
    moveArray(array, oldIndex, newIndex) {
      array.splice(newIndex, 0, ...array.splice(oldIndex, 1));
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
      const oldIndex = this.hasHeaders ? event.oldIndex + 1 : event.oldIndex;
      const newIndex = this.hasHeaders ? event.newIndex + 1 : event.newIndex;
      this.moveRow(oldIndex, newIndex);
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
      this.$panel.dialog.open({
				component: "k-remove-dialog",
				props: {
					text: this.$t('field.table.delete.confirm.column')
				},
				on: {
					submit: () => {
						this.tableData.forEach((column) => column.splice(columnIndex, 1));
            this.updateTable();
						this.$panel.dialog.close();
					}
				}
			});
    },
    removeRow(rowIndex) {
      this.$panel.dialog.open({
				component: "k-remove-dialog",
				props: {
					text: this.$t('field.table.delete.confirm.row')
				},
				on: {
					submit: () => {
            const currentIndex = this.hasHeaders ? rowIndex + 1 : rowIndex;
						this.tableData.splice(currentIndex, 1);
            this.updateTable();
						this.$panel.dialog.close();
					}
				}
			});
    },
    insertColumn(columnIndex, insert = "before") {
      const insertIndex = insert === "before" ? columnIndex : columnIndex + 1;
      this.columns.splice(insertIndex, 0, '');
      this.rows.forEach((row) => row.splice(insertIndex, 0, ''));
      this.updateTable();
    },
    insertRow(rowIndex, insert = "before") {
      const currentIndex = this.hasHeaders ? rowIndex + 1 : rowIndex;
      const insertIndex = insert === "before" ? currentIndex : currentIndex + 1;
      this.tableData.splice(insertIndex, 0, Array(this.columns.length).fill(''));
      this.updateTable();
    },
    duplicateColumn(columnIndex) {
      this.columns.splice(columnIndex + 1, 0, this.columns[columnIndex]);
      this.rows.forEach((row) => row.splice(columnIndex + 1, 0, row[columnIndex]));
      this.updateTable();
    },
    duplicateRow(rowIndex) {
      const currentIndex = this.hasHeaders ? rowIndex + 1 : rowIndex;
      this.tableData.splice(currentIndex, 0, [...this.tableData[currentIndex]]);
      this.updateTable();
    },
    removeAll() {
      this.$panel.dialog.open({
				component: "k-remove-dialog",
				props: {
					text: this.$t('field.table.delete.confirm.all')
				},
				on: {
					submit: () => {
						this.columns.splice(0, this.columns.length, ...Array.from({ length: this.minColumns }, () => ''));
            this.tableData.splice(0, this.tableData.length, ...[Array(this.minColumns).fill('')]);
						this.updateTable();
						this.$panel.dialog.close();
					}
				}
			});
    },
    updateTable() {
      this.$emit('input', this.tableData);
    }
  }
};
</script>
