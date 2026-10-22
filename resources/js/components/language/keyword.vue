<template>
  <div class="keyword-page">
    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <crm-page-header
              title="Từ khóa"
              description="Enter để thêm và nhập tiếp. Tab để chuyển giữa các ô dịch."
            >
              <vs-button color="success" type="filled" :disabled="saving" @click="saveTranslations">Lưu bản dịch</vs-button>
            </crm-page-header>

            <form class="keyword-composer" @submit.prevent="addKeyword">
              <div class="keyword-composer-grid">
                <label class="keyword-field">
                  <span>Từ khóa</span>
                  <input
                    ref="keyInput"
                    v-model="draft.languageKey"
                    class="form-control"
                    placeholder="vi_du_tu_khoa"
                    autocomplete="off"
                    @input="draft.languageKey = normalizeKey(draft.languageKey)"
                  >
                </label>
                <label class="keyword-field">
                  <span>Giá trị mặc định</span>
                  <input
                    v-model="draft.languageDefaultValue"
                    class="form-control"
                    placeholder="Nội dung gốc"
                    autocomplete="off"
                  >
                </label>
                <label v-for="language in languages" :key="'draft-' + language.code" class="keyword-field">
                  <span>{{ language.name }}</span>
                  <input
                    v-model="draft.lang[language.code]"
                    class="form-control"
                    :placeholder="language.name"
                    autocomplete="off"
                  >
                </label>
                <div class="keyword-field keyword-field--action">
                  <span class="keyword-field-spacer">Thêm</span>
                  <vs-button color="primary" type="filled" button="submit" :disabled="saving">Thêm và nhập tiếp</vs-button>
                </div>
              </div>
              <p v-if="keyExists" class="keyword-hint is-error">Từ khóa này đã có. Hãy dùng tên khác.</p>
              <p v-else class="keyword-hint">Khoảng trắng sẽ đổi thành dấu gạch dưới. Để trống giá trị mặc định thì lấy bản dịch đầu tiên.</p>
            </form>

            <div class="keyword-bulk">
              <button type="button" class="keyword-bulk-toggle" @click="showBulk = !showBulk">
                {{ showBulk ? 'Đóng dán nhiều dòng' : 'Dán nhiều từ khóa một lúc' }}
              </button>
              <form v-if="showBulk" @submit.prevent="addBulk">
                <textarea
                  v-model="bulkText"
                  class="form-control"
                  rows="5"
                  :placeholder="bulkPlaceholder"
                ></textarea>
                <div class="keyword-bulk-actions">
                  <vs-button color="primary" type="filled" button="submit" :disabled="saving || !bulkText.trim()">Thêm các dòng</vs-button>
                </div>
              </form>
            </div>

            <crm-filter-bar class="crm-list-filter-bar">
              <div class="row w-100 crm-list-filter-row">
                <div class="col-12 col-md-4 mb-2">
                  <input
                    v-model="keyword"
                    class="form-control crm-list-filter-control"
                    placeholder="Lọc từ khóa đã có"
                  >
                </div>
                <div class="col-12 col-md-3 mb-2 d-flex align-items-center">
                  <span class="keyword-count">{{ filteredRows.length }} / {{ rows.length }} từ khóa</span>
                </div>
              </div>
            </crm-filter-bar>

            <div class="table-responsive keyword-table-wrap">
              <table class="table table-bordered table-sm mb-0">
                <thead>
                  <tr>
                    <th class="keyword-col-stt">STT</th>
                    <th class="keyword-col-key">Từ khóa</th>
                    <th>Giá trị mặc định</th>
                    <th v-for="language in languages" :key="'head-' + language.code">{{ language.name }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, index) in filteredRows" :key="item.info.languageKey">
                    <td>{{ index + 1 }}</td>
                    <td><code>{{ item.info.languageKey }}</code></td>
                    <td>
                      <input v-model="item.info.languageDefaultValue" class="form-control" autocomplete="off">
                    </td>
                    <td v-for="language in languages" :key="item.info.languageKey + '-' + language.code">
                      <input v-model="item.lang[language.code]" class="form-control" autocomplete="off">
                    </td>
                  </tr>
                  <tr v-if="filteredRows.length === 0">
                    <td :colspan="3 + languages.length" class="text-center text-muted">Chưa có từ khóa phù hợp</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapActions } from "vuex";

export default {
  data() {
    return {
      languages: [],
      rows: [],
      keyword: "",
      draft: {
        languageKey: "",
        languageDefaultValue: "",
        lang: {}
      },
      bulkText: "",
      showBulk: false,
      saving: false,
      originalDefaults: {}
    };
  },
  computed: {
    filteredRows() {
      const key = (this.keyword || "").trim().toLowerCase();
      if (!key) return this.rows;
      return this.rows.filter(item => {
        const bag = [
          item.info.languageKey,
          item.info.languageDefaultValue,
          ...Object.values(item.lang || {})
        ].join(" ").toLowerCase();
        return bag.indexOf(key) !== -1;
      });
    },
    keyExists() {
      const key = this.normalizeKey(this.draft.languageKey);
      return !!key && this.rows.some(item => item.info.languageKey === key);
    },
    bulkPlaceholder() {
      const names = this.languages.map(language => language.name).join(" | ");
      return names
        ? "Mỗi dòng: tu_khoa | giá trị mặc định | " + names
        : "Mỗi dòng: tu_khoa | giá trị mặc định";
    }
  },
  methods: {
    ...mapActions(["loadings", "listLanguage", "getListLanguageStatic", "saveLanguageStatic", "saveLanguageStaticByLang"]),
    normalizeKey(value) {
      return String(value || "")
        .trim()
        .replace(/\s+/g, "_")
        .replace(/[^A-Za-z0-9_.-]/g, "");
    },
    emptyLang() {
      const lang = {};
      this.languages.forEach(language => {
        lang[language.code] = "";
      });
      return lang;
    },
    resetDraft() {
      this.draft = {
        languageKey: "",
        languageDefaultValue: "",
        lang: this.emptyLang()
      };
    },
    focusKey() {
      this.$nextTick(() => {
        if (this.$refs.keyInput) this.$refs.keyInput.focus();
      });
    },
    normalizeRows(list) {
      return (Array.isArray(list) ? list : []).map(item => {
        const lang = this.emptyLang();
        const source = item.lang || {};
        Object.keys(source).forEach(code => {
          lang[code] = source[code] == null ? "" : source[code];
        });
        return {
          info: {
            languageKey: item.info ? item.info.languageKey : "",
            languageDefaultValue: item.info && item.info.languageDefaultValue ? item.info.languageDefaultValue : ""
          },
          lang
        };
      });
    },
    rememberDefaults() {
      const defaults = {};
      this.rows.forEach(item => {
        defaults[item.info.languageKey] = item.info.languageDefaultValue || "";
      });
      this.originalDefaults = defaults;
    },
    loadPage() {
      this.loadings(true);
      Promise.all([
        this.listLanguage({ name: "", status: -1 }),
        this.getListLanguageStatic()
      ]).then(([languageResponse, staticResponse]) => {
        const languages = Array.isArray(languageResponse.data) ? languageResponse.data : [];
        this.languages = languages
          .filter(item => Number(item.status) === 1)
          .sort((a, b) => Number(a.priority) - Number(b.priority));
        this.rows = this.normalizeRows(staticResponse.data);
        this.rememberDefaults();
        this.resetDraft();
      }).catch(() => {
        this.$error("Không tải được danh sách từ khóa.");
      }).finally(() => {
        this.loadings(false);
        this.focusKey();
      });
    },
    buildEntry(key, defaultValue, lang) {
      const translations = Object.assign(this.emptyLang(), lang || {});
      let fallback = (defaultValue || "").trim();
      if (!fallback) {
        const first = this.languages.map(language => translations[language.code]).find(value => String(value || "").trim());
        fallback = first ? String(first).trim() : key;
      }
      if (this.languages[0] && !String(translations[this.languages[0].code] || "").trim()) {
        translations[this.languages[0].code] = fallback;
      }
      return {
        info: {
          languageKey: key,
          languageDefaultValue: fallback
        },
        lang: translations
      };
    },
    addKeyword() {
      const key = this.normalizeKey(this.draft.languageKey);
      if (!key) {
        this.$error("Nhập từ khóa trước khi thêm.");
        this.focusKey();
        return;
      }
      if (this.rows.some(item => item.info.languageKey === key)) {
        this.$error("Từ khóa đã tồn tại.");
        this.focusKey();
        return;
      }
      const entry = this.buildEntry(key, this.draft.languageDefaultValue, this.draft.lang);
      this.persistNewEntries([entry]);
    },
    addBulk() {
      const entries = [];
      const seen = {};
      this.rows.forEach(item => {
        seen[item.info.languageKey] = true;
      });
      const lines = this.bulkText.split(/\r?\n/);
      lines.forEach(line => {
        const trimmed = line.trim();
        if (!trimmed) return;
        const cells = (trimmed.indexOf("|") !== -1 ? trimmed.split("|") : trimmed.split("\t"))
          .map(cell => cell.trim());
        const key = this.normalizeKey(cells[0]);
        if (!key || seen[key]) return;
        const lang = this.emptyLang();
        this.languages.forEach((language, index) => {
          lang[language.code] = cells[index + 2] || "";
        });
        seen[key] = true;
        entries.push(this.buildEntry(key, cells[1] || "", lang));
      });
      if (!entries.length) {
        this.$error("Không có dòng hợp lệ. Dòng trùng hoặc thiếu từ khóa đã được bỏ qua.");
        return;
      }
      this.persistNewEntries(entries, true);
    },
    persistNewEntries(entries, fromBulk) {
      const next = entries.concat(this.rows);
      this.saving = true;
      this.loadings(true);
      entries.reduce((chain, entry) => {
        return chain.then(() => this.saveLanguageStatic({
          languageKey: entry.info.languageKey,
          languageDefaultValue: entry.info.languageDefaultValue
        }));
      }, Promise.resolve())
        .then(() => this.saveLanguageStaticByLang(JSON.stringify(next)))
        .then(response => {
          if (!response || !response.success) {
            this.$error("Đã tạo từ khóa nhưng chưa lưu được bản dịch.");
            return;
          }
          this.rows = next;
          this.rememberDefaults();
          if (fromBulk) {
            this.bulkText = "";
            this.showBulk = false;
            this.$success("Đã thêm " + entries.length + " từ khóa.");
          } else {
            this.resetDraft();
            this.$success("Đã thêm từ khóa.");
          }
          this.focusKey();
        })
        .catch(() => {
          this.$error("Không thêm được từ khóa.");
        })
        .finally(() => {
          this.saving = false;
          this.loadings(false);
        });
    },
    saveTranslations() {
      const changed = this.rows.filter(item => {
        return (this.originalDefaults[item.info.languageKey] || "") !== (item.info.languageDefaultValue || "");
      });
      this.saving = true;
      this.loadings(true);
      changed.reduce((chain, item) => {
        return chain.then(() => this.saveLanguageStatic({
          languageKey: item.info.languageKey,
          languageDefaultValue: item.info.languageDefaultValue
        }));
      }, Promise.resolve())
        .then(() => this.saveLanguageStaticByLang(JSON.stringify(this.rows)))
        .then(response => {
          if (response && response.success) {
            this.rememberDefaults();
            this.$success("Đã lưu bản dịch.");
          }
        })
        .catch(() => {
          this.$error("Lưu bản dịch thất bại.");
        })
        .finally(() => {
          this.saving = false;
          this.loadings(false);
        });
    }
  },
  mounted() {
    this.loadPage();
  }
};
</script>

<style scoped>
.keyword-composer {
  margin-bottom: 12px;
  padding: 12px;
  border: 1px solid #dbe7df;
  border-radius: 12px;
  background: #f7fbf8;
}

.keyword-composer-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 10px;
  align-items: end;
}

.keyword-field {
  display: flex;
  flex-direction: column;
  gap: 4px;
  margin: 0;
  font-size: 12px;
  font-weight: 600;
  color: #3d5348;
}

.keyword-field input {
  height: 36px;
  font-weight: 400;
}

.keyword-field-spacer {
  visibility: hidden;
}

.keyword-hint {
  margin: 8px 0 0;
  font-size: 12px;
  color: #6e786f;
}

.keyword-hint.is-error {
  color: #8a1c1c;
}

.keyword-bulk {
  margin-bottom: 12px;
}

.keyword-bulk-toggle {
  border: 0;
  padding: 0;
  background: transparent;
  color: #173f32;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
}

.keyword-bulk textarea {
  margin-top: 8px;
  font-size: 13px;
}

.keyword-bulk-actions {
  margin-top: 8px;
}

.keyword-count {
  font-size: 12px;
  color: #6e786f;
}

.keyword-table-wrap {
  max-height: calc(100vh - 360px);
  overflow: auto;
}

.keyword-table-wrap thead th {
  position: sticky;
  top: 0;
  z-index: 1;
  background: #fff;
}

.keyword-col-stt {
  width: 54px;
}

.keyword-col-key {
  min-width: 160px;
}

.keyword-table-wrap input {
  min-width: 140px;
  height: 32px;
  font-size: 13px;
}
</style>
