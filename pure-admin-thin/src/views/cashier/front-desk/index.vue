<template>
  <div class="front-desk-container">
    <el-card>
      <!-- 标签页 -->
      <el-tabs v-model="activeTab" @tab-remove="removeTab">
        <!-- 服务列表 -->
        <el-tab-pane name="service">
          <template #label>
            <el-icon class="mr-1"
              ><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024">
                <path
                  fill="currentColor"
                  d="M160 320v448a32 32 0 0032 32h640a32 32 0 0032-32V320a32 32 0 00-32-32H192a32 32 0 00-32 32zm640 416H192v-384h608v384zm-32-304a32 32 0 01-32-32V160a32 32 0 0132-32h64a32 32 0 0132 32v240a32 32 0 01-32 32h-64z"
                /></svg
            ></el-icon>
            服务收银列表
          </template>
          <div class="tab-content">
            <!-- 搜索栏和操作按钮 -->
            <div class="mb-4 flex justify-between items-center">
              <div class="flex items-center">
                <el-segmented
                  v-model="serviceStatusFilter"
                  :options="serviceStatusOptions"
                  size="default"
                  style="margin-right: 16px"
                  @change="filterCustomerServices"
                />
                <el-tag
                  size="large"
                  class="font-bold"
                  style="margin-right: 0; font-size: 14px"
                  ><el-icon class="mr-1"
                    ><svg
                      xmlns="http://www.w3.org/2000/svg"
                      viewBox="0 0 1024 1024"
                    >
                      <path
                        fill="currentColor"
                        d="M512 128a384 384 0 100 768 384 384 0 000-768zm-64 448a64 64 0 11128 0 64 64 0 01-128 0zm128-192a64 64 0 110 128 64 64 0 010-128z"
                      /></svg></el-icon
                  >会员收银</el-tag
                >
                <el-select
                  v-model="selectedCustomer"
                  filterable
                  remote
                  reserve-keyword
                  placeholder="请输入客户姓名/电话/会员卡号"
                  :remote-method="remoteMethod"
                  :loading="customerLoading"
                  style="width: 300px; margin-left: 0"
                  @change="handleSelectCustomer"
                >
                  <el-option
                    v-for="customer in customerOptions"
                    :key="customer.id"
                    :label="`${customer.name} (${customer.phone}) ${customer.memberId}`"
                    :value="customer"
                  />
                </el-select>
              </div>
              <div class="flex items-center gap-4">
                <el-button type="success" @click="handleWalkInCustomer">
                  <el-icon class="mr-1"
                    ><svg
                      xmlns="http://www.w3.org/2000/svg"
                      viewBox="0 0 1024 1024"
                    >
                      <path
                        fill="currentColor"
                        d="M832 448H672v-192h96a32 32 0 100-64h-96v-64h-32a32 32 0 00-32 32v64h-192v-64a32 32 0 00-32-32h-32v64h-96a32 32 0 100 64h96v192H192a32 32 0 00-32 32v256a32 32 0 0032 32h640a32 32 0 0032-32V480a32 32 0 00-32-32zm-384-192h128v192h-128v-192zm-128 448h64v-256h64v256h-128zm384 0h-64v-256h64v256zm128 0h-128v-256h64v256h64z"
                      /></svg></el-icon
                  >散客收银</el-button
                >
                <el-button type="primary" @click="handleCreateCustomer">
                  <el-icon class="mr-1"
                    ><svg
                      xmlns="http://www.w3.org/2000/svg"
                      viewBox="0 0 1024 1024"
                    >
                      <path
                        fill="currentColor"
                        d="M512 64C264.6 64 64 264.6 64 512s200.6 448 448 448 448-200.6 448-448S759.4 64 512 64zm174.6 633.8L350.6 442.2a32 32 0 010-45.4l336-255.4a32 32 0 0140.8 50.4L414.1 424.4a32 32 0 01-14.1 51.9l299.3 166.4a32 32 0 11-29.6 55.8z"
                      /></svg></el-icon
                  >创建客户</el-button
                >
              </div>
            </div>

            <!-- 服务列表 -->
            <div class="mb-6">
              <el-table
                v-loading="customerServicesLoading"
                :data="filteredCustomerServices"
                style="width: 100%"
              >
                <el-table-column prop="customerName" label="顾客姓名" />
                <el-table-column prop="appointmentTime" label="预约时间" />
                <el-table-column prop="checkinTime" label="签到时间" />
                <el-table-column prop="room" label="房间" />
                <el-table-column prop="beautician" label="服务人" />
                <el-table-column prop="serviceProgress" label="服务进度">
                  <template #default="scope">
                    <el-tag
                      :type="getServiceProgressType(scope.row.serviceProgress)"
                    >
                      {{ scope.row.serviceProgress }}
                    </el-tag>
                  </template>
                </el-table-column>
                <el-table-column prop="serviceStartTime" label="开始时间" />
                <el-table-column prop="serviceEndTime" label="结束时间" />
                <el-table-column label="操作" width="180">
                  <template #default="scope">
                    <el-button
                      v-if="scope.row.serviceProgress === '已预约'"
                      type="primary"
                      size="small"
                      @click="handleCustomerCheckin(scope.row)"
                    >
                      签到
                    </el-button>
                    <el-button
                      v-else-if="
                        ['已签到', '服务中'].includes(scope.row.serviceProgress)
                      "
                      type="info"
                      size="small"
                      @click="viewCustomerOrder(scope.row)"
                    >
                      查看
                    </el-button>
                    <el-button
                      type="danger"
                      size="small"
                      @click="deleteCustomerService(scope.row.id)"
                    >
                      删除
                    </el-button>
                  </template>
                </el-table-column>
              </el-table>

              <!-- 分页 -->
              <div class="pagination">
                <el-pagination
                  v-model:current-page="servicePagination.current"
                  v-model:page-size="servicePagination.pageSize"
                  :page-sizes="[10, 20, 50, 100]"
                  layout="total, sizes, prev, pager, next, jumper"
                  :total="servicePagination.total"
                  @size-change="handleServiceSizeChange"
                  @current-change="handleServiceCurrentChange"
                />
              </div>
            </div>
          </div>
        </el-tab-pane>

        <!-- 动态生成的标签页 -->
        <el-tab-pane
          v-for="tab in dynamicTabs"
          :key="tab.id"
          :label="tab.label"
          :name="tab.id"
          :closable="true"
        >
          <div class="tab-content p-4">
            <!-- 统一的顾客结算页面 -->
            <template
              v-if="
                (tab.type === 'walk-in' || tab.type === 'customer') &&
                tab.customer
              "
            >
              <div class="customer-billing-page">
                <!-- 顶部信息区域：左右分栏 -->
                <el-row :gutter="12" class="mb-3">
                  <!-- 左侧：顾客基础信息 -->
                  <el-col :span="14">
                    <el-card size="small" class="customer-info-section">
                      <template #header>
                        <div class="card-header">
                          <span>顾客基础信息</span>
                        </div>
                      </template>
                      <!-- 操作区域：部门选项卡和按钮 -->
                      <div class="flex justify-between items-center mb-2">
                        <!-- 部门选项卡 -->
                        <el-segmented
                          v-model="tab.department"
                          :options="departmentOptions"
                          size="default"
                        />
                        <!-- 操作按钮 -->
                        <div class="flex items-center">
                          <el-checkbox
                            v-model="tab.isNewMember"
                            size="default"
                            style="margin-right: 16px"
                          >
                            新会员
                          </el-checkbox>
                          <el-button
                            type="primary"
                            size="small"
                            @click="openConsumptionRecord(tab.customer)"
                          >
                            查看消费记录
                          </el-button>
                          <el-button
                            type="info"
                            size="small"
                            style="margin-left: 8px"
                            @click="openCardBalance(tab.customer)"
                          >
                            查看客户卡余
                          </el-button>
                        </div>
                      </div>

                      <el-descriptions :column="3" size="default" border>
                        <el-descriptions-item label="顾客姓名">{{
                          tab.customer?.name || ""
                        }}</el-descriptions-item>
                        <el-descriptions-item label="手机号">{{
                          tab.customer?.phone || ""
                        }}</el-descriptions-item>
                        <el-descriptions-item label="会员号">{{
                          tab.customer?.memberId || ""
                        }}</el-descriptions-item>
                        <el-descriptions-item label="会员等级">{{
                          tab.customer?.memberLevel || ""
                        }}</el-descriptions-item>
                        <el-descriptions-item label="护理喜好">{{
                          tab.customer?.preferences || ""
                        }}</el-descriptions-item>
                        <el-descriptions-item label="禁忌">{{
                          tab.customer?.taboos || ""
                        }}</el-descriptions-item>
                      </el-descriptions>

                      <!-- 订单备注 -->
                      <el-form size="default" :inline="false" class="mt-2">
                        <el-form-item label="订单备注">
                          <el-input
                            v-model="tab.serviceInfo.remark"
                            type="textarea"
                            placeholder="请输入订单备注"
                            size="default"
                            :rows="2"
                            style="width: 100%"
                          />
                        </el-form-item>
                      </el-form>
                    </el-card>
                  </el-col>
                  <!-- 右侧：服务进度管理 -->
                  <el-col :span="10">
                    <el-card size="small" class="service-progress-section">
                      <template #header>
                        <div class="card-header">
                          <span>服务进度管理</span>
                        </div>
                      </template>

                      <!-- 服务进度选项卡和保存按钮 -->
                      <div class="flex justify-between items-center mb-4">
                        <!-- 服务进度选项卡 -->
                        <el-segmented
                          v-model="tab.serviceProgressStatus"
                          :options="serviceProgressOptions"
                          size="default"
                        />
                        <!-- 保存按钮 -->
                        <el-button
                          type="primary"
                          size="medium"
                          style="margin-right: 4px"
                          @click="saveServiceInfo(tab)"
                        >
                          保存
                        </el-button>
                      </div>

                      <!-- 保存状态提示 -->
                      <el-alert
                        v-if="saveStatus[tab.id]"
                        :type="saveStatus[tab.id].type"
                        :title="saveStatus[tab.id].message"
                        show-icon
                        :closable="false"
                        :duration="2000"
                        class="mb-2"
                      />

                      <!-- 服务详情 -->
                      <el-form size="default" border class="progress-info">
                        <div class="flex flex-wrap gap-2">
                          <div class="flex-1 min-w-[210px]">
                            <el-form-item label="预约时间" :label-width="70">
                              <el-date-picker
                                v-model="tab.serviceInfo.appointmentTime"
                                type="datetime"
                                placeholder="选择预约时间"
                                size="small"
                                style="width: 210px"
                              />
                            </el-form-item>
                          </div>
                          <div class="flex-1 min-w-[210px]">
                            <el-form-item label="签到时间" :label-width="70">
                              <el-date-picker
                                v-model="tab.serviceInfo.checkinTime"
                                type="datetime"
                                placeholder="选择签到时间"
                                size="small"
                                style="width: 210px"
                              />
                            </el-form-item>
                          </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                          <div class="flex-1 min-w-[210px]">
                            <el-form-item label="开始时间" :label-width="70">
                              <el-date-picker
                                v-model="tab.serviceInfo.serviceStartTime"
                                type="datetime"
                                placeholder="选择开始时间"
                                size="small"
                                style="width: 210px"
                              />
                            </el-form-item>
                          </div>
                          <div class="flex-1 min-w-[210px]">
                            <el-form-item label="结束时间" :label-width="70">
                              <el-date-picker
                                v-model="tab.serviceInfo.serviceEndTime"
                                type="datetime"
                                placeholder="选择结束时间"
                                size="small"
                                style="width: 210px"
                              />
                            </el-form-item>
                          </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                          <div class="flex-1 min-w-[210px]">
                            <el-form-item label="服务房间" :label-width="70">
                              <el-select
                                v-model="tab.serviceInfo.room"
                                placeholder="选择房间"
                                filterable
                                size="small"
                                style="width: 210px"
                              >
                                <el-option label="101" value="101" />
                                <el-option label="102" value="102" />
                                <el-option label="103" value="103" />
                                <el-option label="104" value="104" />
                                <el-option label="105" value="105" />
                              </el-select>
                            </el-form-item>
                          </div>
                          <div class="flex-1 min-w-[210px]">
                            <el-form-item label="服务人员" :label-width="70">
                              <el-select
                                v-model="tab.serviceInfo.beautician"
                                placeholder="选择服务人"
                                filterable
                                multiple
                                size="small"
                                style="width: 210px"
                              >
                                <el-option label="小红" value="小红" />
                                <el-option label="小明" value="小明" />
                                <el-option label="小丽" value="小丽" />
                                <el-option label="小张" value="小张" />
                                <el-option label="小王" value="小王" />
                                <el-option label="小李" value="小李" />
                                <el-option label="小刘" value="小刘" />
                                <el-option label="小陈" value="小陈" />
                              </el-select>
                            </el-form-item>
                          </div>
                        </div>
                      </el-form>
                    </el-card>
                  </el-col>
                </el-row>

                <!-- 下方：结算类型选项卡 -->
                <el-card size="small" class="payment-section">
                  <el-tabs v-model="tab.activePaymentTab" size="small">
                    <el-tab-pane name="cash">
                      <template #label>
                        <el-icon class="mr-1"
                          ><svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 1024 1024"
                          >
                            <path
                              fill="currentColor"
                              d="M832 448H672v-192h96a32 32 0 100-64h-96v-64h-32a32 32 0 00-32 32v64h-192v-64a32 32 0 00-32-32h-32v64h-96a32 32 0 100 64h96v192H192a32 32 0 00-32 32v256a32 32 0 0032 32h640a32 32 0 0032-32V480a32 32 0 00-32-32zm-384-192h128v192h-128v-192zm-128 448h64v-256h64v256h-128zm384 0h-64v-256h64v256zm128 0h-128v-256h64v256h64z"
                            /></svg
                        ></el-icon>
                        现金单次消费
                      </template>
                      <div class="payment-content">
                        <p>现金单次消费结算页面</p>
                      </div>
                    </el-tab-pane>
                    <el-tab-pane name="recharge-card">
                      <template #label>
                        <el-icon class="mr-1"
                          ><svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 1024 1024"
                          >
                            <path
                              fill="currentColor"
                              d="M160 320v448a32 32 0 0032 32h640a32 32 0 0032-32V320a32 32 0 00-32-32H192a32 32 0 00-32 32zm640 416H192v-384h608v384zm-32-304a32 32 0 01-32-32V160a32 32 0 0132-32h64a32 32 0 0132 32v240a32 32 0 01-32 32h-64z"
                            /></svg
                        ></el-icon>
                        充值卡划卡
                      </template>
                      <div class="payment-content">
                        <p>充值卡划卡结算页面</p>
                      </div>
                    </el-tab-pane>
                    <el-tab-pane name="card-consumption">
                      <template #label>
                        <el-icon class="mr-1"
                          ><svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 1024 1024"
                          >
                            <path
                              fill="currentColor"
                              d="M512 64C264.6 64 64 264.6 64 512s200.6 448 448 448 448-200.6 448-448S759.4 64 512 64zm174.6 633.8L350.6 442.2a32 32 0 010-45.4l336-255.4a32 32 0 0140.8 50.4L414.1 424.4a32 32 0 01-14.1 51.9l299.3 166.4a32 32 0 11-29.6 55.8z"
                            /></svg
                        ></el-icon>
                        卡消耗
                      </template>
                      <div class="payment-content">
                        <p>卡消耗结算页面</p>
                      </div>
                    </el-tab-pane>
                    <el-tab-pane name="open-card">
                      <template #label>
                        <el-icon class="mr-1"
                          ><svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 1024 1024"
                          >
                            <path
                              fill="currentColor"
                              d="M896 128H128a32 32 0 00-32 32v704a32 32 0 0032 32h768a32 32 0 0032-32V160a32 32 0 00-32-32zM128 192h768v640H128V192zm320 288a32 32 0 0132 32v128a32 32 0 11-64 0v-128a32 32 0 0132-32z"
                            /></svg
                        ></el-icon>
                        客户开卡
                      </template>
                      <div class="payment-content">
                        <p>客户开卡结算页面</p>
                      </div>
                    </el-tab-pane>
                    <el-tab-pane name="repay-debt">
                      <template #label>
                        <el-icon class="mr-1"
                          ><svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 1024 1024"
                          >
                            <path
                              fill="currentColor"
                              d="M832 448H672v-192h96a32 32 0 100-64h-96v-64h-32a32 32 0 00-32 32v64h-192v-64a32 32 0 00-32-32h-32v64h-96a32 32 0 100 64h96v192H192a32 32 0 00-32 32v256a32 32 0 0032 32h640a32 32 0 0032-32V480a32 32 0 00-32-32zm-384-192h128v192h-128v-192zm-128 448h64v-256h64v256h-128zm384 0h-64v-256h64v256zm128 0h-128v-256h64v256h64z"
                            /></svg
                        ></el-icon>
                        还欠款
                      </template>
                      <div class="payment-content">
                        <p>还欠款结算页面</p>
                      </div>
                    </el-tab-pane>
                    <el-tab-pane name="transfer-recharge">
                      <template #label>
                        <el-icon class="mr-1"
                          ><svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 1024 1024"
                          >
                            <path
                              fill="currentColor"
                              d="M160 320v448a32 32 0 0032 32h640a32 32 0 0032-32V320a32 32 0 00-32-32H192a32 32 0 00-32 32zm640 416H192v-384h608v384zm-32-304a32 32 0 01-32-32V160a32 32 0 0132-32h64a32 32 0 0132 32v240a32 32 0 01-32 32h-64z"
                            /></svg
                        ></el-icon>
                        转卡续充
                      </template>
                      <div class="payment-content">
                        <p>转卡续充结算页面</p>
                      </div>
                    </el-tab-pane>
                  </el-tabs>
                </el-card>
              </div>
            </template>
          </div>
        </el-tab-pane>
      </el-tabs>

      <!-- 顾客消费记录弹窗 -->
      <el-dialog
        v-model="consumptionRecordDialogVisible"
        title="顾客消费记录"
        width="80%"
        :fullscreen="false"
        custom-class="consumption-record-dialog"
      >
        <div v-if="selectedCustomerForRecord">
          <!-- 搜索条件 -->
          <el-card class="mb-4" shadow="hover">
            <el-form :inline="true" class="mb-0" size="small">
              <el-form-item label="分店">
                <el-select
                  v-model="searchForm.branch"
                  placeholder="请选择分店"
                  style="width: 120px"
                >
                  <el-option label="总店" value="总店" />
                  <el-option label="分店1" value="分店1" />
                  <el-option label="分店2" value="分店2" />
                </el-select>
              </el-form-item>
              <el-form-item label="部门">
                <el-select
                  v-model="searchForm.department"
                  placeholder="请选择部门"
                  style="width: 120px"
                >
                  <el-option label="美容部" value="美容部" />
                  <el-option label="美发部" value="美发部" />
                  <el-option label="spa部" value="spa部" />
                </el-select>
              </el-form-item>
              <el-form-item label="日期">
                <el-date-picker
                  v-model="searchForm.dateRange"
                  type="daterange"
                  range-separator="至"
                  start-placeholder="开始日期"
                  end-placeholder="结束日期"
                  size="small"
                  style="width: 220px"
                />
              </el-form-item>
              <el-form-item label="消费类型">
                <el-select
                  v-model="searchForm.consumptionType"
                  placeholder="请选择类型"
                  style="width: 100px"
                >
                  <el-option label="消费" value="消费" />
                  <el-option label="消耗" value="消耗" />
                  <el-option label="充值" value="充值" />
                </el-select>
              </el-form-item>
              <el-form-item>
                <el-button type="primary" size="small">查询</el-button>
                <el-button size="small">重置</el-button>
              </el-form-item>
            </el-form>
          </el-card>

          <!-- 合并后的消费记录表格 -->
          <el-card shadow="hover">
            <div style="overflow-x: auto">
              <el-table :data="mergedRecords" style="width: 100%" stripe>
                <el-table-column prop="branch" label="分店" min-width="80" />
                <el-table-column
                  prop="department"
                  label="部门"
                  min-width="80"
                />
                <el-table-column prop="date" label="日期" min-width="140" />
                <el-table-column
                  prop="consumptionType"
                  label="消费类型"
                  min-width="80"
                />
                <el-table-column prop="cardType" label="卡项" min-width="90" />
                <el-table-column
                  prop="projectName"
                  label="项目名"
                  min-width="120"
                />
                <el-table-column
                  prop="servicePerson"
                  label="服务人"
                  min-width="80"
                />
                <el-table-column
                  prop="consumptionAmount"
                  label="消费金额"
                  min-width="90"
                  align="right"
                />
                <el-table-column
                  prop="consumptionQuantity"
                  label="消耗金额"
                  min-width="90"
                  align="right"
                />
                <el-table-column
                  prop="nonCashConsumption"
                  label="非现款消费"
                  min-width="100"
                  align="right"
                />
                <el-table-column
                  prop="paymentMethod"
                  label="付款方式"
                  min-width="80"
                />
                <el-table-column
                  prop="arrearsAmount"
                  label="欠费金额"
                  min-width="90"
                  align="right"
                />
              </el-table>
            </div>

            <!-- 分页 -->
            <div class="pagination mt-4">
              <el-pagination
                v-model:current-page="recordPagination.current"
                v-model:page-size="recordPagination.pageSize"
                :page-sizes="[10, 20, 50, 100]"
                layout="total, sizes, prev, pager, next, jumper"
                :total="recordPagination.total"
                @size-change="handleRecordSizeChange"
                @current-change="handleRecordCurrentChange"
              />
            </div>
          </el-card>
        </div>
        <div v-else>
          <el-empty description="请选择顾客" />
        </div>
      </el-dialog>

      <!-- 顾客卡余弹窗 -->
      <el-dialog
        v-model="cardBalanceDialogVisible"
        title="顾客卡余信息"
        width="600px"
      >
        <div v-if="selectedCustomerForRecord">
          <h4 class="mb-4">{{ selectedCustomerForRecord.name }} 的卡余信息</h4>

          <!-- 卡余信息 -->
          <div class="card-balance-dialog">
            <div class="balance-list-dialog">
              <div class="balance-item-dialog">
                <span class="balance-type-dialog">充值卡</span>
                <span class="balance-amount-dialog"
                  >¥{{ selectedCustomerForRecord.balance || 0 }}</span
                >
              </div>
              <div class="balance-item-dialog">
                <span class="balance-type-dialog">套餐卡</span>
                <span class="balance-amount-dialog"
                  >{{ selectedCustomerForRecord.packageCards || 0 }} 次</span
                >
              </div>
              <div class="balance-item-dialog">
                <span class="balance-type-dialog">时效卡</span>
                <span class="balance-amount-dialog"
                  >{{ selectedCustomerForRecord.timeCards || 0 }} 天</span
                >
              </div>
            </div>
          </div>
        </div>
        <div v-else>
          <el-empty description="请选择顾客" />
        </div>
      </el-dialog>
    </el-card>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from "vue";
import { ElMessage } from "element-plus";

// 当前激活的标签页
const activeTab = ref("service");

// 动态标签页
const dynamicTabs = ref([]);

// 服务搜索表单
const serviceSearchForm = reactive({});

// 客户列表（模拟数据）
const customerList = ref([
  {
    id: 1,
    name: "张三",
    phone: "13800138001",
    memberId: "VIP001",
    memberLevel: "钻石会员",
    balance: 1000,
    points: 2000,
    initial: "ZS",
    beautician: "小红",
    preferences: "喜欢精油护理发斯蒂芬斯蒂芬是的水电费第三方",
    taboos: "对薰衣草精油过敏",
    packageCards: 5,
    timeCards: 30
  },
  {
    id: 2,
    name: "李四",
    phone: "13800138002",
    memberId: "VIP002",
    memberLevel: "黄金会员",
    balance: 500,
    points: 1000,
    initial: "LS",
    beautician: "小明",
    preferences: "喜欢面部护理",
    taboos: "暂无",
    packageCards: 3,
    timeCards: 15
  },
  {
    id: 3,
    name: "王五",
    phone: "13800138003",
    memberId: "VIP003",
    memberLevel: "普通会员",
    balance: 0,
    points: 500,
    initial: "WW",
    beautician: "小丽",
    preferences: "喜欢身体护理",
    taboos: "暂无",
    packageCards: 0,
    timeCards: 0
  },
  {
    id: 4,
    name: "赵六",
    phone: "13800138004",
    memberId: "VIP004",
    memberLevel: "白银会员",
    balance: 200,
    points: 800,
    initial: "ZL",
    beautician: "小红",
    preferences: "喜欢肩颈按摩",
    taboos: "颈部有伤口",
    packageCards: 2,
    timeCards: 10
  },
  {
    id: 5,
    name: "孙七",
    phone: "13800138005",
    memberId: "VIP005",
    memberLevel: "黄金会员",
    balance: 800,
    points: 1500,
    initial: "SQ",
    beautician: "小张",
    preferences: "喜欢足疗",
    taboos: "暂无",
    packageCards: 4,
    timeCards: 20
  }
]);

// 客户搜索相关
const selectedCustomer = ref(null);
const customerOptions = ref([]);
const customerLoading = ref(false);

// 初始化客户选项
const initCustomerOptions = () => {
  // 默认显示前10个顾客数据
  customerOptions.value = customerList.value.slice(0, 10);
};

// 组件挂载时初始化
initCustomerOptions();

// 服务状态筛选
const serviceStatusFilter = ref("全部");
const serviceStatusOptions = ref([
  "全部",
  "已预约",
  "已签到",
  "服务中",
  "已完成",
  "已取消"
]);

// 服务进度选项
const serviceProgressOptions = ref([
  "请选择",
  "已预约",
  "已签到",
  "服务中",
  "已完成",
  "已取消"
]);

// 顾客消费记录弹窗
const consumptionRecordDialogVisible = ref(false);
const selectedCustomerForRecord = ref(null);

// 顾客卡余弹窗
const cardBalanceDialogVisible = ref(false);

// 搜索表单数据模型
const searchForm = reactive({
  branch: "",
  department: "",
  dateRange: [],
  consumptionType: ""
});

// 消费记录分页
const recordPagination = reactive({
  current: 1,
  pageSize: 10,
  total: 5
});

// 部门选项
const departmentOptions = ref(["美发部", "美容部"]);

// 合并后的消费记录数据
const mergedRecords = ref([
  {
    id: 1,
    branch: "总店",
    department: "美容部",
    date: "2026-02-01 10:30",
    consumptionType: "消费",
    cardType: "充值卡",
    projectName: "面部护理",
    servicePerson: "小红",
    consumptionAmount: 200,
    consumptionQuantity: 0,
    nonCashConsumption: 0,
    paymentMethod: "现金",
    arrearsAmount: 0
  },
  {
    id: 2,
    branch: "总店",
    department: "美容部",
    date: "2026-01-25 14:20",
    consumptionType: "消费",
    cardType: "充值卡",
    projectName: "购买护肤品",
    servicePerson: "小明",
    consumptionAmount: 150,
    consumptionQuantity: 0,
    nonCashConsumption: 0,
    paymentMethod: "微信支付",
    arrearsAmount: 0
  },
  {
    id: 3,
    branch: "总店",
    department: "美容部",
    date: "2026-01-20 09:00",
    consumptionType: "充值",
    cardType: "充值卡",
    projectName: "充值",
    servicePerson: "小红",
    consumptionAmount: 1000,
    consumptionQuantity: 0,
    nonCashConsumption: 0,
    paymentMethod: "现金",
    arrearsAmount: 0
  },
  {
    id: 4,
    branch: "总店",
    department: "美容部",
    date: "2025-12-15 15:30",
    consumptionType: "充值",
    cardType: "充值卡",
    projectName: "充值",
    servicePerson: "小丽",
    consumptionAmount: 500,
    consumptionQuantity: 0,
    nonCashConsumption: 0,
    paymentMethod: "支付宝",
    arrearsAmount: 0
  },
  {
    id: 5,
    branch: "总店",
    department: "美容部",
    date: "2026-01-10 11:00",
    consumptionType: "消耗",
    cardType: "套餐卡",
    projectName: "全身按摩",
    servicePerson: "小张",
    consumptionAmount: 0,
    consumptionQuantity: 1,
    nonCashConsumption: 0,
    paymentMethod: "套餐卡",
    arrearsAmount: 0
  }
]);

// 打开消费记录弹窗
const openConsumptionRecord = customer => {
  selectedCustomerForRecord.value = customer;
  consumptionRecordDialogVisible.value = true;
};

// 打开卡余弹窗
const openCardBalance = customer => {
  selectedCustomerForRecord.value = customer;
  cardBalanceDialogVisible.value = true;
};

// 编辑记录
const editRecord = record => {
  ElMessage.info("编辑记录");
};

// 删除记录
const deleteRecord = id => {
  ElMessage.info("删除记录");
};

// 保存状态管理
const saveStatus = ref({});

// 保存服务信息
const saveServiceInfo = tab => {
  // 模拟保存操作
  setTimeout(() => {
    saveStatus.value[tab.id] = {
      type: "success",
      message: "保存成功"
    };
    // 2秒后清除保存状态
    setTimeout(() => {
      delete saveStatus.value[tab.id];
    }, 2000);
  }, 500);
};

// 自动保存功能（可以根据需要扩展）
const autoSaveServiceInfo = tab => {
  // 这里可以添加自动保存逻辑，例如使用watch监听数据变化
  console.log("自动保存服务信息", tab.id);
};

// 顾客服务列表
const customerServices = ref([
  {
    id: 1,
    customerName: "张三",
    serviceProgress: "已预约",
    appointmentTime: "2026-02-09 09:00",
    checkinTime: "",
    room: "101",
    beautician: "小红",
    serviceStartTime: "",
    serviceEndTime: ""
  },
  {
    id: 2,
    customerName: "李四",
    serviceProgress: "已签到",
    appointmentTime: "2026-02-09 10:30",
    checkinTime: "2026-02-09 10:25",
    room: "102",
    beautician: "小明",
    serviceStartTime: "2026-02-09 10:35",
    serviceEndTime: ""
  },
  {
    id: 3,
    customerName: "王五",
    serviceProgress: "服务中",
    appointmentTime: "2026-02-09 14:00",
    checkinTime: "2026-02-09 13:55",
    room: "103",
    beautician: "小丽",
    serviceStartTime: "2026-02-09 14:05",
    serviceEndTime: ""
  },
  {
    id: 4,
    customerName: "赵六",
    serviceProgress: "已完成",
    appointmentTime: "2026-02-09 09:30",
    checkinTime: "2026-02-09 09:25",
    room: "101",
    beautician: "小红",
    serviceStartTime: "2026-02-09 09:35",
    serviceEndTime: "2026-02-09 10:35"
  },
  {
    id: 5,
    customerName: "孙七",
    serviceProgress: "已签到",
    appointmentTime: "2026-02-09 16:30",
    checkinTime: "2026-02-09 16:25",
    room: "102",
    beautician: "小张",
    serviceStartTime: "2026-02-09 16:35",
    serviceEndTime: ""
  }
]);

const customerServicesLoading = ref(false);

// 服务分页
const servicePagination = reactive({
  current: 1,
  pageSize: 10,
  total: 5
});

// 过滤后的顾客服务列表
const filteredCustomerServices = computed(() => {
  let filtered = [];
  if (serviceStatusFilter.value === "全部") {
    filtered = customerServices.value;
  } else {
    filtered = customerServices.value.filter(
      service => service.serviceProgress === serviceStatusFilter.value
    );
  }

  // 排序：已完成和已取消的订单在最下方
  return filtered.sort((a, b) => {
    const statusOrder = {
      已预约: 1,
      已签到: 2,
      服务中: 3,
      已完成: 4,
      已取消: 5
    };
    return (
      (statusOrder[a.serviceProgress] || 999) -
      (statusOrder[b.serviceProgress] || 999)
    );
  });
});

// 服务搜索方法已移除，使用分段控制直接筛选

// 客户搜索远程方法
const remoteMethod = query => {
  if (query !== "") {
    customerLoading.value = true;
    // 模拟远程搜索
    setTimeout(() => {
      customerLoading.value = false;
      // 过滤客户列表
      customerOptions.value = customerList.value.filter(customer => {
        const searchTerm = query.toLowerCase();
        return (
          customer.name.toLowerCase().includes(searchTerm) ||
          customer.phone.includes(searchTerm) ||
          customer.memberId.toLowerCase().includes(searchTerm) ||
          (customer.initial &&
            customer.initial.toLowerCase().includes(searchTerm))
        );
      });
    }, 200);
  } else {
    // 输入为空时显示默认的10个顾客数据
    customerOptions.value = customerList.value.slice(0, 10);
  }
};

// 创建标签页的公共函数
const createTab = (customer, tabType, tabId, tabLabel) => {
  const now = new Date().toISOString().slice(0, 19).replace("T", " ");

  // 创建新标签页
  const newTab = {
    id: tabId,
    label: tabLabel,
    type: tabType,
    customer: customer,
    department: "美发部",
    serviceProgressStatus: "请选择",
    serviceInfo: {
      appointmentTime: now,
      checkinTime: now,
      room: "",
      beautician: customer.beautician ? [customer.beautician] : [],
      serviceStartTime: "",
      serviceEndTime: "",
      remark: ""
    },
    activePaymentTab: "cash"
  };

  // 添加到动态标签页数组
  dynamicTabs.value.push(newTab);
  // 激活新标签页
  activeTab.value = tabId;

  return newTab;
};

// 处理选择客户
const handleSelectCustomer = () => {
  if (selectedCustomer.value) {
    const customer = selectedCustomer.value;
    // 生成唯一ID
    const tabId = `customer-${customer.id}`;
    // 检查是否已存在该客户的标签页
    const existingTabIndex = dynamicTabs.value.findIndex(
      tab => tab.id === tabId
    );

    // 添加到服务列表
    const now = new Date().toISOString().slice(0, 19).replace("T", " ");

    if (existingTabIndex === -1) {
      // 创建新标签页
      createTab(customer, "customer", tabId, customer.name);

      const newService = {
        id: Date.now(),
        customerName: customer.name,
        serviceProgress: "已签到",
        appointmentTime: now,
        checkinTime: now,
        room: "",
        beautician: customer.beautician || "默认服务人",
        serviceStartTime: "",
        serviceEndTime: ""
      };
      customerServices.value.push(newService);
      // 更新服务总数
      servicePagination.total = customerServices.value.length;
    } else {
      // 激活已存在的标签页
      activeTab.value = tabId;
    }

    // 重置选择
    selectedCustomer.value = null;
  }
};

// 处理散客收银
const handleWalkInCustomer = () => {
  // 生成唯一ID
  const tabId = `walk-in-${Date.now()}`;
  const now = new Date();
  const dateStr = now.toISOString().slice(0, 10).replace(/-/g, "");
  const timeStr = now.toISOString().slice(11, 19).replace(/:/g, "");
  const fullDateStr = dateStr + timeStr;

  // 创建散客对象，确保数据结构与选择顾客一致
  const walkInCustomer = {
    name: `散客${fullDateStr}`,
    phone: "",
    memberId: "",
    memberLevel: "",
    preferences: "",
    taboos: "",
    beautician: "" // 确保散客对象也有beautician字段
  };

  // 创建新标签页
  createTab(
    walkInCustomer,
    "walk-in",
    tabId,
    `散客收银 ${dynamicTabs.value.length + 1}`
  );
};

// 处理创建客户
const handleCreateCustomer = () => {
  ElMessage.info("创建客户功能暂未实现");
};

// 处理顾客签到
const handleCustomerCheckin = service => {
  // 找到对应的顾客信息
  const customer = customerList.value.find(
    c => c.name === service.customerName
  );
  if (customer) {
    // 生成唯一ID
    const tabId = `customer-${customer.id}`;
    // 检查是否已存在该客户的标签页
    const existingTabIndex = dynamicTabs.value.findIndex(
      tab => tab.id === tabId
    );

    if (existingTabIndex === -1) {
      // 创建新标签页
      const newTab = {
        id: tabId,
        label: customer.name,
        type: "customer",
        customer: customer,
        department: "美发部",
        serviceProgressStatus: "请选择",
        serviceInfo: {
          appointmentTime: service.appointmentTime,
          checkinTime: service.checkinTime,
          room: service.room,
          beautician: Array.isArray(service.beautician)
            ? service.beautician
            : service.beautician
              ? [service.beautician]
              : [],
          serviceStartTime: service.serviceStartTime,
          serviceEndTime: service.serviceEndTime,
          remark: ""
        },
        activePaymentTab: "cash"
      };
      // 添加到动态标签页数组
      dynamicTabs.value.push(newTab);
      // 激活新标签页
      activeTab.value = tabId;
    } else {
      // 激活已存在的标签页
      activeTab.value = tabId;
    }

    ElMessage.info(`为顾客 ${service.customerName} 签到`);
  }
};

// 查看顾客订单
const viewCustomerOrder = service => {
  // 找到对应的顾客信息
  const customer = customerList.value.find(
    c => c.name === service.customerName
  );
  if (customer) {
    // 生成唯一ID
    const tabId = `customer-${customer.id}`;
    // 检查是否已存在该客户的标签页
    const existingTabIndex = dynamicTabs.value.findIndex(
      tab => tab.id === tabId
    );

    if (existingTabIndex === -1) {
      // 创建新标签页
      const newTab = {
        id: tabId,
        label: customer.name,
        type: "customer",
        customer: customer,
        department: "美发部",
        serviceProgressStatus: "请选择",
        serviceInfo: {
          appointmentTime: service.appointmentTime,
          checkinTime: service.checkinTime,
          room: service.room,
          beautician: Array.isArray(service.beautician)
            ? service.beautician
            : service.beautician
              ? [service.beautician]
              : [],
          serviceStartTime: service.serviceStartTime,
          serviceEndTime: service.serviceEndTime,
          remark: ""
        },
        activePaymentTab: "cash"
      };
      // 添加到动态标签页数组
      dynamicTabs.value.push(newTab);
      // 激活新标签页
      activeTab.value = tabId;
    } else {
      // 激活已存在的标签页
      activeTab.value = tabId;
    }

    ElMessage.info(`查看顾客 ${service.customerName} 的订单`);
  }
};

// 删除顾客服务
const deleteCustomerService = id => {
  ElMessage.info(`删除服务 ID：${id}`);
};

// 删除标签页
const removeTab = tabId => {
  const index = dynamicTabs.value.findIndex(tab => tab.id === tabId);
  if (index !== -1) {
    dynamicTabs.value.splice(index, 1);
    // 如果删除的是当前激活的标签页，激活服务列表标签页
    if (activeTab.value === tabId) {
      activeTab.value = "service";
    }
  }
};

// 获取服务进度类型
const getServiceProgressType = progress => {
  const typeMap = {
    已预约: "info",
    已签到: "primary",
    服务中: "success",
    已完成: "warning",
    已取消: "danger"
  };
  return typeMap[progress] || "default";
};

// 分页方法
const handleServiceSizeChange = size => {
  servicePagination.pageSize = size;
};

const handleServiceCurrentChange = current => {
  servicePagination.current = current;
};

// 筛选顾客服务
const filterCustomerServices = () => {
  // 筛选逻辑已在computed中实现
};

// 消费记录分页方法
const handleRecordSizeChange = size => {
  recordPagination.pageSize = size;
};

const handleRecordCurrentChange = current => {
  recordPagination.current = current;
};
</script>

<style scoped>
.front-desk-container {
  height: calc(100vh - 120px);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.el-card {
  height: 100%;
  display: flex;
  flex-direction: column;
}

:deep(.el-card__body) {
  padding: 16px;
  height: 100%;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

:deep(.el-tabs) {
  height: 100%;
  display: flex;
  flex-direction: column;
}

:deep(.el-tabs__content) {
  flex: 1;
  overflow: auto;
}

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.pagination {
  display: flex;
  justify-content: flex-end;
  margin-top: 10px;
}

/* 顾客开单页面样式 */
.customer-billing-page {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
}

/* 顶部信息区域：左右分栏 */
.customer-billing-page .el-row {
  margin-bottom: 12px;
}

/* 左侧：顾客基础信息 */
.customer-info-section {
  height: 100%;
}

/* 右侧：服务进度管理 */
.service-progress-section {
  height: 100%;
}

/* 下方：结算管理 */
.payment-section {
  width: 100%;
  flex: 1;
  min-height: 0;
}

:deep(.payment-section .el-card__body) {
  padding: 16px;
  height: 100%;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

:deep(.payment-section .el-tabs) {
  height: 100%;
  display: flex;
  flex-direction: column;
}

:deep(.payment-section .el-tabs__content) {
  flex: 1;
  overflow: auto;
}

/* 结算页面样式 */
.payment-content {
  padding: 15px;
}

/* 顾客消费记录弹窗样式 */
.consumption-record-dialog .el-dialog__body {
  padding: 20px;
}

/* 卡余弹窗样式 */
.card-balance-dialog {
  padding: 20px;
}

.balance-list-dialog {
  display: flex;
  justify-content: space-around;
  flex-wrap: wrap;
}

.balance-item-dialog {
  text-align: center;
  margin-bottom: 16px;
  min-width: 120px;
  padding: 12px;
  background-color: #f9fafc;
  border-radius: 4px;
  border: 1px solid #ebeef5;
}

.balance-type-dialog {
  font-size: 12px;
  color: #606266;
  margin-bottom: 8px;
  display: block;
}

.balance-amount-dialog {
  font-size: 18px;
  font-weight: bold;
  color: #303133;
}

/* 选项卡圆角样式 */
:deep(.el-segmented) {
  border-radius: 4px !important;
  overflow: hidden;
}

:deep(.el-segmented__item-selected) {
  border-radius: 4px !important;
  overflow: hidden;
}

:deep(.el-segmented__item:first-child .el-segmented__item-label) {
  border-radius: 4px 0 0 4px !important;
}

:deep(.el-segmented__item:last-child .el-segmented__item-label) {
  border-radius: 0 4px 4px 0 !important;
}

:deep(.el-tabs__nav) {
  border-radius: 4px 4px 0 0 !important;
  overflow: hidden;
}

:deep(.el-tabs__active-bar) {
  border-radius: 0 !important;
}

:deep(.el-tab-pane) {
  border-radius: 0 0 4px 4px !important;
  overflow: hidden;
}

/* 服务列表页面样式 */
.tab-content {
  height: 100%;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* 服务列表区域 */
.tab-content > div:nth-child(2) {
  flex: 1;
  min-height: 0;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* 服务列表表格 */
.tab-content .el-table {
  flex: 1;
  min-height: 0;
}

:deep(.tab-content .el-table__body-wrapper) {
  overflow: auto;
}
</style>
