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
				  @click="add('column')"
			  />
        <k-button
					icon="dots"
					size="xs"
					variant="filled"
					@click="$refs.options.toggle()"
				/>
				<k-dropdown-content
					ref="options"
					:options="tableOptions"
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
            @end="onDrag($event, 'column')"
          >
            <th
              v-if="hasIndexColumn"
              data-mobile="true"
              class="k-table-index-column"
            >
              #
            </th>

            <!-- Header Column -->
            <th
              v-for="(column, columnIndex) in columns"
              :key="columnIndex + '-header'"
              :data-align="hasAlignment"
              data-mobile="true"
              :class="['k-table-column', 'k-table-header', { 'k-table-sortable-row': isSortable }]"
            >
              <k-bar>
                <k-sort-handle v-if="isSortable" class="k-table-sort-handle" />

                <k-text-input
                  v-if="hasHeaders"
                  v-model="columns[columnIndex]"
                  :disabled="disabled"
                  :spellcheck="false"
                  :placeholder="`${$t('field.table.column')} ${columnIndex + 1}`"
                  @input="update()"
                />
                <k-text v-else>{{ $t('field.table.column') }} {{ columnIndex + 1 }}</k-text>

                <k-options-dropdown
                  v-if="!disabled"
                  :options="columnOptions"
                  @option="columnOption($event, columnIndex)"
								/>
              </k-bar>
            </th>

            <th
              v-if="hasOptions"
              data-mobile="true"
              class="k-table-options-column"
            ></th>
          </k-draggable>
        </thead>

        <!-- Rows -->
        <k-draggable
          :list="values"
          :options="dragOptions"
          :handle="true"
          element="tbody"
          @end="onDrag($event, 'row')"
        >
          <!-- Empty -->
				  <tr v-if="rows.length === 0">
					  <td :colspan="colspan" class="k-table-empty">
						  {{ empty ?? $t('field.table.empty.rows') }}
					  </td>
				  </tr>

          <template v-else>
            <tr
              v-for="(row, rowIndex) in rows"
              :key="rowIndex"
              :class="['k-table-row', { 'k-table-sortable-row': isSortable && rows.length > 1 }]"
            >
              <!-- Index & drag handle -->
              <td
                v-if="hasIndexColumn"
                :data-sortable="isSortable && rows.length > 1"
                data-mobile="true"
                class="k-table-index-column"
              >
								<div class="k-table-index" v-text="index + rowIndex" />

                <k-sort-handle
                  v-if="isSortable && rows.length > 1"
                  class="k-table-sort-handle"
                />
              </td>

              <!-- Column -->
              <td
                v-for="(column, columnIndex) in row"
                :key="rowIndex + '-' + columnIndex"
                :data-align="hasAlignment"
                data-mobile="true"
                class="k-table-column k-table-cell"
              >
                <k-writer-input
                  v-model="row[columnIndex]"
                  :disabled="disabled"
                  :inline="true"
                  :nodes="false"
                  :marks="marks"
                  :spellcheck="false"
                  @input="update()"
                />
              </td>

              <!-- Options -->
              <td
                v-if="hasOptions"
                data-mobile="true"
                class="k-table-options-column"
              >
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
        @click="add('row')"
      />
    </footer>
  </k-field>
</template>

<script>
export default {
  extends: 'k-field',
  props: {
    align: String,
    duplicate: {
      type: Boolean,
      default: true
    },
    empty: String,
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
      default: 8
    },
    marks: {
      type: [Boolean, Array],
      default: true
    },
    sortable: {
      type: Boolean,
      default: true
    },
    value: [String, Array]
  },
  computed: {
    values() {
      return [...this.table];
    },
    columns() {
      return this.hasHeaders
        ? this.table[0]
        : this.table[0].length > 0
          ? Array(this.table[0].length).fill('')
          : Array(this.minColumns).fill('');
    },
    rows() {
      return this.hasHeaders ? this.table.slice(1) : this.table;
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
    hasAlignment() {
      return this.align;
    },
    hasOptions() {
      return !this.disabled && this.rows.length !== 0;
    },
    hasIndexColumn() {
			return this.isSortable || this.index !== false;
		},
    isSortable() {
      return !this.disabled && this.index !== false && this.sortable;
    },
    table() {
      const clearValue = (value) => value.trim().replace(/^[>\|-]?\s*["']?(.*?)["']?$/, '$1') || '';
      const isRowBreak = (value) => /^\s*-\s*$/.test(value);
      const isMultiline = (value) => /^\s*-\s*[>|]\s*$/.test(value);

      const parseRows = (value) => {
        const rows = [];
        let currentRow = [];
        let currentCell = '';
        let inMultilineCell = false;

        value.split('\n').forEach((line) => {
          if (isRowBreak(line)) {
            if (inMultilineCell) {
              currentRow.push(clearValue(currentCell));
              currentCell = '';
              inMultilineCell = false;
            }
            if (currentRow.length > 0) {
              rows.push(currentRow);
              currentRow = [];
            }
          } else if (isMultiline(line)) {
            if (inMultilineCell) {
              currentRow.push(clearValue(currentCell));
              currentCell = '';
            }
            inMultilineCell = true;
          } else if (inMultilineCell) {
            if (line.trim().startsWith('-') && !line.trim().startsWith('- >')) {
              inMultilineCell = false;
              currentRow.push(clearValue(currentCell));
              currentCell = '';
              if (!isRowBreak(line)) {
                currentRow.push(clearValue(line));
              }
            } else {
              currentCell += (currentCell ? '\n' : '') + line.trim();
            }
          } else {
            currentRow.push(clearValue(line));
          }
        });

        if (inMultilineCell && currentCell.trim()) {
          currentRow.push(clearValue(currentCell));
        }

        if (currentRow.length > 0) rows.push(currentRow);

        return rows;
      };

      let rows = typeof this.value === 'string' ? parseRows(this.value) : this.value;

      rows ||= Array.from({ length: this.hasHeaders ? 2 : 1 }, () => Array(this.minColumns).fill(''));
      rows.forEach(row => {
        while (row.length < this.minColumns) row.push('');
      });

      return rows;
    },
    dragOptions() {
			return {
        disabled: !this.sortable,
        draggable: ".k-table-sortable-row",
        fallbackClass: 'k-table-row-fallback',
        ghostClass: 'k-table-row-ghost'
			};
		},
    tableOptions() {
      return [
				{
					click: () => this.remove('all'),
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
          icon: 'angle-left',
          text: this.$t('insert.before'),
          click: 'insertBefore'
        },
        {
          disabled: this.columns.length >= this.maxColumns,
          icon: 'angle-right',
          text: this.$t('insert.after'),
          click: 'insertAfter'
        },
        '-',
        {
          icon: 'eraser',
          text: this.$t('field.table.clear'),
          click: 'clear'
        },
        {
          disabled: !this.duplicate || this.columns.length >= this.maxColumns,
          icon: 'copy',
          text: this.$t('duplicate'),
          click: 'duplicate'
        },
        '-',
        {
          disabled: this.columns.length <= this.minColumns,
          icon: 'trash',
          text: this.$t('delete'),
          click: 'remove'
        }
      ];
    },
    rowOptions() {
      return [
        {
          icon: 'angle-up',
          text: this.$t('insert.before'),
          click: 'insertBefore'
        },
        {
          icon: 'angle-down',
          text: this.$t('insert.after'),
          click: 'insertAfter'
        },
        '-',
        {
          icon: 'eraser',
          text: this.$t('field.table.clear'),
          click: 'clear'
        },
        {
          disabled: !this.duplicate,
          icon: 'copy',
          text: this.$t('duplicate'),
          click: 'duplicate'
        },
        '-',
        {
          disabled: this.rows.length <= 1,
          icon: 'trash',
          text: this.$t('delete'),
          click: 'remove'
        }
      ];
    },
  },
  methods: {
    columnOption(option, columnIndex) {
      switch (option) {
        case 'insertBefore':
          this.insert('column', columnIndex, 'before')
          break;
        case 'insertAfter':
          this.insert('column', columnIndex, 'after')
          break;
        case 'clear':
          this.clear('column', columnIndex)
          break;
        case 'duplicate':
          this.duplicate('column', columnIndex)
          break;
        case 'remove':
          this.remove('column', columnIndex);
          break;
      }
    },
    rowOption(option, rowIndex) {
      switch (option) {
        case 'insertBefore':
          this.insert('row', rowIndex, 'before')
          break;
        case 'insertAfter':
          this.insert('row', rowIndex, 'after')
          break;
        case 'clear':
          this.clear('row', rowIndex)
          break;
        case 'duplicate':
          this.duplicate('row', rowIndex)
          break;
        case 'remove':
          this.remove('row', rowIndex);
          break;
      }
    },
    showDialog(message, callback) {
      this.$panel.dialog.open({
        component: 'k-remove-dialog',
        props: { text: message },
        on: {
          submit: () => {
            callback();
            this.update();
            this.$panel.dialog.close();
          }
        }
      });
    },
    add(type) {
      if (type === 'column') {
        this.table.forEach(column => column.push(''));
      } else if (type === 'row') {
        this.table.push(Array(this.columns.length).fill(''));
      }

      this.update();
    },
    insert(type, index, insert = 'before') {
      const currentIndex = (type === 'column') ? index : (this.hasHeaders ? index + 1 : index);
      const insertIndex = (insert === 'before') ? currentIndex : currentIndex + 1;

      if (type === 'column') {
        this.columns.splice(insertIndex, 0, '');
        this.rows.forEach(column => column.splice(insertIndex, 0, ''));
      } else if (type === 'row') {
        this.table.splice(insertIndex, 0, Array(this.columns.length).fill(''));
      }

      this.update();
    },
    clear(type, index) {
      const currentIndex = this.hasHeaders ? index + 1 : index;

      if (type === 'column') {
        this.table.forEach(column => column[index] = '');
      } else if (type === 'row') {
        this.table[currentIndex].fill('');
      }

      this.update();
    },
    duplicate(type, index) {
      const currentIndex = this.hasHeaders ? index + 1 : index;

      if (type === 'column') {
        this.columns.splice(index + 1, 0, this.columns[index]);
        this.rows.forEach(column => column.splice(index + 1, 0, column[index]));
      } else if (type === 'row') {
        this.table.splice(currentIndex, 0, [...this.table[currentIndex]]);
      }

      this.update();
    },
    remove(type, index) {
      if (type === 'column') {
        const message = this.$t('field.table.delete.confirm.column');
        this.showDialog(message, () => {
          this.table.forEach(column => column.splice(index, 1));
        });
      } else if (type === 'row') {
        const message = this.$t('field.table.delete.confirm.row');
        this.showDialog(message, () => {
          const currentIndex = this.hasHeaders ? index + 1 : index;
          this.table.splice(currentIndex, 1);
        });
      } else if (type === 'all') {
        const message = this.$t('field.table.delete.confirm.all');
        this.showDialog(message, () => {
          this.columns.splice(0, this.columns.length, Array(this.minColumns).fill(''));
          this.table.splice(0, this.table.length, Array(this.minColumns).fill(''));
        });
      }
    },
    onDrag(event, type) {
      const currentIndex = (type === 'column') ? -1 : (this.hasHeaders ? 1 : 0);
      const oldIndex = event.oldIndex + currentIndex;
      const newIndex = event.newIndex + currentIndex;

      if (type === 'column') {
        this.columns.splice(newIndex, 0, ...this.columns.splice(oldIndex, 1));
        this.rows.forEach(column => column.splice(newIndex, 0, ...column.splice(oldIndex, 1)));
      } else if (type === 'row') {
        this.table.splice(newIndex, 0, ...this.table.splice(oldIndex, 1));
      }

      this.update();
    },
    update() {
      this.$emit('input', this.table);
    }
  }
};
</script>
