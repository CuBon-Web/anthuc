<template>
  <div>
      <h3 class="page-title">Thêm mới trang nội dung</h3>
      <div class="row">
        <div class="col-md-8 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <div class="form-group">
                <label>Tên trang nội dung</label>
                <vs-input
                  type="text"
                  size="default"
                  placeholder="Tên bài viết"
                  class="w-100"
                  v-model="objData.title[0].content"
                />
                <el-button size="small" @click="showSettingLang('title')">Đa ngôn ngữ</el-button>
                <div class="dropLanguage" v-if="showLang.title">
                  <div class="form-group" v-for="(item, index) in lang" :key="'page-title-' + index">
                    <label v-if="index != 0">{{ item.name }}</label>
                    <input
                      v-if="index != 0 && objData.title[index]"
                      type="text"
                      class="w-100 inputlang"
                      v-model="objData.title[index].content"
                    />
                  </div>
                </div>
                <div v-if="submitted && !primaryText(objData.title)" class="noti-err">Title không để trống</div>
              </div>
              <div class="form-group">
                <label>Nội dung</label>
                <TinyMce v-model="objData.content[0].content" />
                <el-button size="small" @click="showSettingLang('content')">Đa ngôn ngữ</el-button>
                <div class="dropLanguage" v-if="showLang.content">
                  <div class="form-group" v-for="(item, index) in lang" :key="'page-content-' + index">
                    <label v-if="index != 0">{{ item.name }}</label>
                    <TinyMce v-if="index != 0 && objData.content[index]" v-model="objData.content[index].content" />
                  </div>
                </div>
                <div v-if="submitted && !primaryText(objData.content)" class="noti-err">Nội dung không để trống</div>
              </div>
              <div class="form-group">
                <label>Mô tả ngắn</label>
                <TinyMce v-model="objData.description[0].content" />
                <el-button size="small" @click="showSettingLang('description')">Đa ngôn ngữ</el-button>
                <div class="dropLanguage" v-if="showLang.description">
                  <div class="form-group" v-for="(item, index) in lang" :key="'page-desc-' + index">
                    <label v-if="index != 0">{{ item.name }}</label>
                    <TinyMce v-if="index != 0 && objData.description[index]" v-model="objData.description[index].content" />
                  </div>
                </div>
                <div v-if="submitted && !primaryText(objData.description)" class="noti-err">Nội dung không để trống</div>
              </div>
              <div class="form-group">
                <label>Ảnh đại diện</label>
                <ImageMulti v-model="objData.image" :title="'trang-noi-dung'"/> 
              </div>
              <vs-button color="primary" @click="addPagecontent">Thêm mới</vs-button>
            </div>
          </div>
          
        </div>
        <div class="col-md-4 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <div class="form-group">
                <label>Trạng thái</label>
                <vs-select v-model="objData.status"
                  >
                      <vs-select-item  value="1" text="Hiện" />
                      <vs-select-item  value="0" text="Ẩn" />
                    </vs-select>
              </div>
              <div class="form-group">
                <label>Danh mục</label>
                <vs-select v-model="objData.type"
                  >
                      <vs-select-item  value="ve-chung-toi" text="Về Chúng Tôi" />  
                      <vs-select-item  value="ho-tro-khanh-hang" text="Hỗ trợ khách hàng" />   
                    </vs-select>
              </div>
              <!-- <div class="form-group">
                <label>Danh muc</label>
                <vs-select class="selectExample" v-model="objData.category" placeholder="Danh mục">
                  <vs-select-item
                    :value="item.id"
                    :text="item.name"
                    v-for="(item,index) in cate"
                    :key="'f'+index"
                  />
                </vs-select>
                <div v-if="submitted && !$v.objData.category.required" class="noti-err">Chọn tối thiểu 1 ảnh</div>
              </div> -->
              
            </div>
          </div>
        </div>
        
      </div>
    <!-- content-wrapper ends -->
  </div>
</template>


<script>
import { mapActions } from "vuex";
import TinyMce from "../_common/tinymce";
import ImageMulti from "../_common/upload_image_multi";

const emptyLang = () => [{ lang_code: "vi", content: "" }];

export default {
  name: "product",
  data() {
    return {
      submitted: false,
      cate:[],
      lang: [],
      showLang: { title: false, content: false, description: false },
      objData: {
        title: emptyLang(),
        content: emptyLang(),
        description: emptyLang(),
        status: 1,
        image: [],
        type:'ve-chung-toi'
      }
    };
  },
  components: {
    TinyMce,ImageMulti
  },
  computed: {},
  watch: {},
  methods: {
    ...mapActions(["savePageContent", "listLanguage", "loadings"]),
    primaryText(field) {
      if (!field || !field[0]) return "";
      return String(field[0].content || "").replace(/<[^>]*>/g, "").trim();
    },
    showSettingLang(field) {
      this.showLang[field] = !this.showLang[field];
      const list = this.objData[field];
      this.lang.forEach((langItem, index) => {
        if (!list[index] && langItem.code !== list[0].lang_code) {
          list.push({ lang_code: langItem.code, content: "" });
        }
      });
    },
    addPagecontent(){
      this.submitted = true;
      if (!this.primaryText(this.objData.title) || !this.primaryText(this.objData.content) || !this.primaryText(this.objData.description)) {
        return;
      }
      this.loadings(true);
      this.savePageContent(this.objData).then(response => {
        this.loadings(false);
        this.$router.push({name:'pageContent'});
        this.$success('Thêm trang nội dung thành công');
      }).catch(error => {
        this.loadings(false);
        this.$error('Thêm trang nội dung thất bại');
      })
    },
    listLang() {
      this.listLanguage({ status: 1 }).then(response => {
        this.lang = response.data || [];
      }).catch(() => {});
    }
  },
  mounted() {
    this.listLang();
  }
};
</script>