<template>
  <div>
    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <crm-page-header
              title="Ngôn ngữ"
              description="Thêm mới hoặc cập nhật ngôn ngữ hiển thị trên website"
            >
              <vs-button type="gradient" @click="popupActivo = true">Thêm ngôn ngữ</vs-button>
            </crm-page-header>

            <crm-filter-bar class="crm-list-filter-bar">
              <div class="row w-100 crm-list-filter-row">
                <div class="col-12 col-md-4 mb-2">
                  <vs-input
                    class="crm-list-filter-control crm-list-filter-keyword"
                    placeholder="Tìm theo tên"
                    v-model="objData.name"
                    @keyup="searchLanguage()"
                  />
                </div>
                <div class="col-12 col-md-3 mb-2">
                  <select class="form-control crm-list-filter-control" v-model="objData.status" @change="listLanguages">
                    <option :value="-1">Tất cả trạng thái</option>
                    <option :value="1">Đang hiện</option>
                    <option :value="0">Đang ẩn</option>
                  </select>
                </div>
              </div>
            </crm-filter-bar>

            <vs-table stripe :data="list" max-items="10" pagination>
              <template slot="thead">
                <vs-th>Cờ</vs-th>
                <vs-th>Mã ngôn ngữ</vs-th>
                <vs-th>Tên</vs-th>
                <vs-th>Thứ tự</vs-th>
                <vs-th>Trạng thái</vs-th>
                <vs-th>Hành động</vs-th>
              </template>
              <template slot-scope="{data}">
                <vs-tr :key="tr.code" v-for="tr in data">
                  <vs-td>
                    <img v-if="tr.flagIcon" class="language-flag" :src="tr.flagIcon" alt="">
                    <span v-else class="text-muted">—</span>
                  </vs-td>
                  <vs-td :data="tr.code">{{ tr.code }}</vs-td>
                  <vs-td :data="tr.name">{{ tr.name }}</vs-td>
                  <vs-td :data="tr.priority">{{ tr.priority }}</vs-td>
                  <vs-td>
                    <span :class="Number(tr.status) === 1 ? 'language-status is-on' : 'language-status is-off'">
                      {{ Number(tr.status) === 1 ? 'Hiện' : 'Ẩn' }}
                    </span>
                  </vs-td>
                  <vs-td>
                    <div class="language-actions">
                      <vs-button vs-type="gradient" size="lagre" color="success" icon="edit" @click="editLang(tr.code)"></vs-button>
                      <vs-button vs-type="gradient" size="lagre" color="red" icon="delete_forever" @click="confirmDestroy(tr.code)"></vs-button>
                    </div>
                  </vs-td>
                </vs-tr>
              </template>
            </vs-table>
          </div>
        </div>
      </div>
    </div>

    <vs-popup title="Thêm ngôn ngữ" :active.sync="popupActivo">
      <AddLanguage @closePopup="closePop($event)" />
    </vs-popup>
    <vs-popup title="Sửa ngôn ngữ" :active.sync="popupEdit">
      <EditLanguage v-if="popupEdit" @closePopupEdit="closePopEdit($event)" :item="detailLang" />
    </vs-popup>
  </div>
</template>

<script>
import { mapActions } from "vuex";
import AddLanguage from "../layouts/modal/language/language";
import EditLanguage from "../layouts/modal/language/language_edit";

export default {
  data() {
    return {
      list: [],
      id_item: "",
      popupActivo: false,
      popupEdit: false,
      objData: {
        name: "",
        status: -1,
        pageIndex: "",
        pageSize: "",
        keyword: ""
      },
      detailLang: {},
      timer: null
    };
  },
  components: { AddLanguage, EditLanguage },
  methods: {
    ...mapActions(["listLanguage", "detailLanguage", "destroyLanguage", "loadings"]),
    closePop(event) {
      this.listLanguages();
      this.popupActivo = event;
    },
    closePopEdit(event) {
      this.listLanguages();
      this.popupEdit = event;
    },
    editLang(code) {
      this.detailLanguage({ code }).then(response => {
        this.detailLang = response.data || {};
        this.popupEdit = true;
      }).catch(() => {
        this.$error("Không tải được thông tin ngôn ngữ.");
      });
    },
    searchLanguage() {
      if (this.timer) {
        clearTimeout(this.timer);
        this.timer = null;
      }
      this.timer = setTimeout(() => {
        this.listLanguages();
      }, 400);
    },
    listLanguages() {
      this.loadings(true);
      this.listLanguage(this.objData)
        .then(response => {
          this.list = Array.isArray(response.data) ? response.data : [];
        })
        .catch(() => {
          this.list = [];
          this.$error("Không tải được danh sách ngôn ngữ.");
        })
        .finally(() => this.loadings(false));
    },
    destroy() {
      this.loadings(true);
      this.destroyLanguage(this.id_item)
        .then(() => {
          this.listLanguages();
          this.$success("Xóa ngôn ngữ thành công");
        })
        .catch(() => {
          this.$error("Xóa ngôn ngữ thất bại.");
        })
        .finally(() => this.loadings(false));
    },
    confirmDestroy(code) {
      this.id_item = code;
      this.$vs.dialog({
        type: "confirm",
        color: "danger",
        title: "Bạn có chắc chắn",
        text: "Xóa ngôn ngữ này",
        accept: this.destroy
      });
    }
  },
  mounted() {
    this.listLanguages();
  }
};
</script>

<style scoped>
.language-actions {
  display: flex;
  align-items: center;
  gap: 6px;
}

.language-flag {
  width: 32px;
  height: 22px;
  object-fit: cover;
  border-radius: 3px;
  border: 1px solid #e5e7eb;
}

.language-status {
  display: inline-flex;
  align-items: center;
  min-height: 22px;
  padding: 0 8px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
}

.language-status.is-on {
  color: #137333;
  background: #e7f6ec;
}

.language-status.is-off {
  color: #8a1c1c;
  background: #fdecec;
}
</style>
