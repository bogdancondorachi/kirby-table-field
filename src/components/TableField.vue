<template>
  <k-field v-bind="$props" class="k-table-field">
    <div class="k-table">
      <table :data-indexed="hasIndexColumn">
        <!-- Headers -->
        <thead>
          <k-draggable
            :list="dragData"
            :options="dragOptions"
            :handle="true"
            element="tr"
            @end="onColumnDrag"
          >
            <th v-if="hasIndexColumn" class="k-table-index-column">#</th>
            <th
              v-for="(column, columnIndex) in columns"
              :key="columnIndex + '-header'"
              :data-sortable="sortable && columns.length > 1"
              class="k-table-column"
            >
              <div class="k-bar">
                <k-sort-handle v-if="sortable && columns.length > 1" class="k-table-sort-handle" />
                <k-text-input
                  v-model="columns[columnIndex]"
                  @input="updateTable()"
                  :placeholder="`Column ${columnIndex + 1}`"
                  type="text"
                />
                <k-button
                  title="Delete column"
                  icon="remove"
                  v-show="columns.length > minColumns"
                  @click="deleteColumn(columnIndex)"
                />
              </div>
            </th>
            <th class="k-table-options-column">
              <k-button
                :disabled="columns.length >= maxColumns"
                title="Add column"
                icon="add"
                @click="addColumn()"
              />
            </th>
          </k-draggable>
        </thead>
        <!-- Rows -->
        <k-draggable
          :list="dragData"
          :options="dragOptions"
          :handle="true"
          element="tbody"
          @end="onRowDrag"
        >
          <!-- Empty -->
				  <tr v-if="rows.length === 0">
					  <td :colspan="colspan" class="k-table-empty">
						  {{ empty }}
					  </td>
				  </tr>

          <template v-else>
            <tr v-for="(row, rowIndex) in rows" :key="rowIndex">
              <!-- Index & drag handle -->
              <td v-if="hasIndexColumn" :data-sortable="sortable && rows.length > 1" class="k-table-index-column">
                <slot name="index">
								  <div class="k-table-index" v-text="index + rowIndex" />
							  </slot>
                <k-sort-handle v-if="sortable && rows.length > 1" class="k-table-sort-handle" />
              </td>
              <!-- Cell -->
              <td v-for="(column, columnIndex) in row" :key="columnIndex" class="k-table-column">
                <k-text-input
                  v-model="row[columnIndex]"
                  @input="updateTable()"
                  type="text"
                />
              </td>
              <!-- Options -->
              <td class="k-table-options-column">
                <k-button
                  title="Delete row"
                  icon="remove"
                  @click="deleteRow(rowIndex)"
                />
              </td>
            </tr>
          </template>
        </k-draggable>
      </table>
    </div>
    <!-- Footer -->
    <footer data-align="center" class="k-bar">
      <k-button
        title="Add row"
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
    disabled: Boolean,
    required: Boolean,
    value: [String, Array],
    
    empty: {
      type: String,
      default: 'No rows yet'
    },
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
    dragData() {
      return [...this.tableData];
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
      const clearValue = (input) => input.trim().replace(/^- /, "").replace(/^[\"\'](.*)[\"\']$/g, "$1");
      const isRowBreak = (input) => input === '- ';

      let array = typeof this.value === 'string'
        ? this.value.split('\n')
          .reduce((row, input) => (
            isRowBreak(input)
              ? row.push([])
              : row[row.length - 1].push(clearValue(input)),
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
    deleteColumn(columnIndex) {
      this.tableData.forEach((column) => column.splice(columnIndex, 1));
      this.updateTable();
    },
    deleteRow(rowIndex) {
      this.tableData.splice(rowIndex + 1, 1);
      this.updateTable();
    },
    updateTable() {
      this.$emit('input', this.tableData);
    }
  }
};
</script>
