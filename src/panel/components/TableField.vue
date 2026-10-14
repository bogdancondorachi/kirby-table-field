<template>
  <k-field
    v-bind="$props"
    :class="['k-table-field', $attrs.class]"
    :input="false"
    :style="$attrs.style"
  >
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
        <k-dropdown
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
            :options="dragOptions"
            :handle="true"
            element="tr"
            @start="onDragStart"
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
              class="k-table-column k-table-header" :class="[{ 'k-table-sortable-row': isSortable }]"
            >
              <k-bar>
                <k-sort-handle v-if="isSortable" class="k-table-sort-handle" />

                <k-text-input
                  v-if="hasHeaders"
                  :disabled="disabled"
                  :spellcheck="false"
                  :placeholder="`${$t('field.table.column')} ${columnIndex + 1}`"
                  :value="column"
                  @input="updateCell(0, columnIndex, $event)"
                />
                <k-text v-else>
                  {{ $t('field.table.column') }} {{ columnIndex + 1 }}
                </k-text>

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
            />
          </k-draggable>
        </thead>

        <!-- Rows -->
        <k-draggable
          :options="dragOptions"
          :handle="true"
          element="tbody"
          @start="onDragStart"
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
              :key="'row-' + rowIndex"
              :class="[{ 'k-table-sortable-row': isSortable && rows.length > 1 }]"
              :data-sortable="isSortable && rows.length > 1"
            >
              <!-- Index & drag handle -->
              <td
                v-if="hasIndexColumn"
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
                v-for="(cell, columnIndex) in row"
                :key="rowIndex + '-' + columnIndex"
                :data-align="hasAlignment"
                data-mobile="true"
                class="k-table-column k-table-cell"
              >
                <k-writer-input
                  :disabled="disabled"
                  :inline="true"
                  :nodes="false"
                  :marks="marks"
                  :spellcheck="false"
                  :value="cell"
                  @input="updateCell(rowOffset + rowIndex, columnIndex, $event)"
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
  extends: "k-field",
  props: {
    align: String,
    duplicate: {
      type: Boolean,
      default: true,
    },
    empty: String,
    headers: {
      type: Boolean,
      default: true,
    },
    index: {
      type: [Number, Boolean],
      default: 1,
    },
    marks: {
      type: [Boolean, Array],
      default: true,
    },
    maxColumns: {
      type: Number,
      default: 8,
    },
    minColumns: {
      type: Number,
      default: 2,
    },
    sortable: {
      type: Boolean,
      default: true,
    },
    value: {
      type: Array,
      default: () => [],
    },
  },
  emits: ["input"],
  data() {
    return {
      dragNextSibling: null,
      table: this.toTable(this.value),
    };
  },
  computed: {
    colspan() {
      let span = this.columns.length;

      if (this.hasIndexColumn === true) {
        span++;
      }

      return span;
    },
    columnOptions() {
      return [
        {
          disabled: this.columns.length >= this.maxColumns,
          icon: "angle-left",
          text: this.$t("insert.before"),
          click: "insertBefore",
        },
        {
          disabled: this.columns.length >= this.maxColumns,
          icon: "angle-right",
          text: this.$t("insert.after"),
          click: "insertAfter",
        },
        "-",
        {
          icon: "eraser",
          text: this.$t("field.table.clear"),
          click: "clear",
        },
        {
          disabled: !this.duplicate || this.columns.length >= this.maxColumns,
          icon: "copy",
          text: this.$t("duplicate"),
          click: "duplicate",
        },
        "-",
        {
          disabled: this.columns.length <= this.minColumns,
          icon: "trash",
          text: this.$t("delete"),
          click: "remove",
        },
      ];
    },
    columns() {
      if (this.hasHeaders === true) {
        return this.table[0];
      }

      return Array.from({ length: this.table[0]?.length ?? this.minColumns }).fill("");
    },
    dragOptions() {
      return {
        disabled: !this.isSortable,
        draggable: ".k-table-sortable-row",
        fallbackClass: "k-table-row-fallback",
        ghostClass: "k-table-row-ghost",
      };
    },
    hasAlignment() {
      return this.align;
    },
    hasHeaders() {
      return this.headers;
    },
    hasIndexColumn() {
      return this.isSortable || this.index !== false;
    },
    hasOptions() {
      return !this.disabled && this.rows.length !== 0;
    },
    isSortable() {
      return !this.disabled && this.index !== false && this.sortable;
    },
    rowOffset() {
      return this.hasHeaders === true ? 1 : 0;
    },
    rowOptions() {
      return [
        {
          icon: "angle-up",
          text: this.$t("insert.before"),
          click: "insertBefore",
        },
        {
          icon: "angle-down",
          text: this.$t("insert.after"),
          click: "insertAfter",
        },
        "-",
        {
          icon: "eraser",
          text: this.$t("field.table.clear"),
          click: "clear",
        },
        {
          disabled: !this.duplicate,
          icon: "copy",
          text: this.$t("duplicate"),
          click: "duplicate",
        },
        "-",
        {
          disabled: this.rows.length <= 1,
          icon: "trash",
          text: this.$t("delete"),
          click: "remove",
        },
      ];
    },
    rows() {
      return this.table.slice(this.rowOffset);
    },
    tableOptions() {
      return [
        {
          click: () => this.remove("all"),
          disabled: this.rows.length === 0 && this.columns.length <= this.minColumns,
          icon: "trash",
          text: this.$t("delete.all"),
        },
      ];
    },
  },
  watch: {
    value(value) {
      if (JSON.stringify(value) !== JSON.stringify(this.table)) {
        this.table = this.toTable(value);
      }
    },
  },
  methods: {
    add(type) {
      if (type === "column") {
        this.table.forEach(row => row.push(""));
      } else if (type === "row") {
        this.table.push(Array.from({ length: this.columns.length }).fill(""));
      }

      this.update();
    },
    clear(type, index) {
      if (type === "column") {
        this.table.forEach(row => row[index] = "");
      } else if (type === "row") {
        this.table[index + this.rowOffset].fill("");
      }

      this.update();
    },
    columnOption(option, columnIndex) {
      switch (option) {
        case "insertBefore":
          this.insert("column", columnIndex, "before");
          break;
        case "insertAfter":
          this.insert("column", columnIndex, "after");
          break;
        case "clear":
          this.clear("column", columnIndex);
          break;
        case "duplicate":
          this.duplicate("column", columnIndex);
          break;
        case "remove":
          this.remove("column", columnIndex);
          break;
      }
    },
    duplicate(type, index) {
      if (type === "column") {
        this.table.forEach(row => row.splice(index + 1, 0, row[index]));
      } else if (type === "row") {
        const currentIndex = index + this.rowOffset;
        this.table.splice(currentIndex, 0, [...this.table[currentIndex]]);
      }

      this.update();
    },
    emptyTable() {
      return Array.from(
        { length: this.headers === true ? 2 : 1 },
        () => Array.from({ length: this.minColumns }).fill(""),
      );
    },
    insert(type, index, insert = "before") {
      const currentIndex = type === "column" ? index : index + this.rowOffset;
      const insertIndex = insert === "before" ? currentIndex : currentIndex + 1;

      if (type === "column") {
        this.table.forEach(row => row.splice(insertIndex, 0, ""));
      } else if (type === "row") {
        this.table.splice(insertIndex, 0, Array.from({ length: this.columns.length }).fill(""));
      }

      this.update();
    },
    onDrag(event, type) {
      const { oldIndex: rawOldIndex, newIndex: rawNewIndex } = event;

      // Put the dragged element back where Vue expects it,
      // the new order gets rendered from the updated table data
      if (event.from && event.item) {
        event.from.insertBefore(event.item, this.dragNextSibling);
      }

      this.dragNextSibling = null;

      if (rawOldIndex === undefined || rawNewIndex === undefined || rawOldIndex === rawNewIndex) {
        return;
      }

      if (type === "column") {
        const offset = this.hasIndexColumn === true ? 1 : 0;
        const maxCol = this.columns.length - 1;
        const validOld = Math.max(0, Math.min(rawOldIndex - offset, maxCol));
        const validNew = Math.max(0, Math.min(rawNewIndex - offset, maxCol));

        if (validOld === validNew) {
          return;
        }

        this.table.forEach((row) => {
          const [col] = row.splice(validOld, 1);
          row.splice(validNew, 0, col);
        });
      } else if (type === "row") {
        const maxRow = this.rows.length - 1;
        const validOld = Math.max(0, Math.min(rawOldIndex, maxRow));
        const validNew = Math.max(0, Math.min(rawNewIndex, maxRow));

        if (validOld === validNew) {
          return;
        }

        const [row] = this.table.splice(validOld + this.rowOffset, 1);
        this.table.splice(validNew + this.rowOffset, 0, row);
      }

      this.update();
    },
    onDragStart(event) {
      this.dragNextSibling = event.item.nextSibling;
    },
    remove(type, index) {
      if (type === "column") {
        this.showDialog(this.$t("field.table.delete.confirm.column"), () => {
          this.table.forEach(row => row.splice(index, 1));
        });
      } else if (type === "row") {
        this.showDialog(this.$t("field.table.delete.confirm.row"), () => {
          this.table.splice(index + this.rowOffset, 1);
        });
      } else if (type === "all") {
        this.showDialog(this.$t("field.table.delete.confirm.all"), () => {
          this.table = this.emptyTable();
        });
      }
    },
    rowOption(option, rowIndex) {
      switch (option) {
        case "insertBefore":
          this.insert("row", rowIndex, "before");
          break;
        case "insertAfter":
          this.insert("row", rowIndex, "after");
          break;
        case "clear":
          this.clear("row", rowIndex);
          break;
        case "duplicate":
          this.duplicate("row", rowIndex);
          break;
        case "remove":
          this.remove("row", rowIndex);
          break;
      }
    },
    showDialog(message, callback) {
      this.$panel.dialog.open({
        component: "k-remove-dialog",
        props: { text: message },
        on: {
          submit: () => {
            callback();
            this.update();
            this.$panel.dialog.close();
          },
        },
      });
    },
    toTable(value) {
      let table = Array.isArray(value)
        ? value.filter(row => Array.isArray(row)).map(row => row.map(cell => cell ?? ""))
        : [];

      if (table.length === 0) {
        return this.emptyTable();
      }

      // Give all rows the same number of columns
      const width = Math.max(this.minColumns, ...table.map(row => row.length));

      table = table.map(row => [
        ...row,
        ...Array.from({ length: width - row.length }).fill(""),
      ]);

      return table;
    },
    update() {
      this.$emit("input", this.table.map(row => [...row]));
    },
    updateCell(rowIndex, columnIndex, value) {
      this.table[rowIndex][columnIndex] = value;
      this.update();
    },
  },
};
</script>
