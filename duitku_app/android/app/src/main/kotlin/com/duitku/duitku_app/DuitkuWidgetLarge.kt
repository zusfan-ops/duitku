package com.duitku.duitku_app

import android.app.PendingIntent
import android.appwidget.AppWidgetManager
import android.content.Context
import android.content.Intent
import android.content.SharedPreferences
import android.view.View
import android.widget.RemoteViews
import es.antonborri.home_widget.HomeWidgetProvider

/**
 * Widget besar 4x4 — dashboard lengkap: saldo, pemasukan/pengeluaran,
 * top 3 kategori pengeluaran, dan tombol aksi cepat [+ Transaksi, Riwayat].
 */
class DuitkuWidgetLarge : HomeWidgetProvider() {

    override fun onUpdate(
        context: Context,
        appWidgetManager: AppWidgetManager,
        appWidgetIds: IntArray,
        widgetData: SharedPreferences,
    ) {
        for (appWidgetId in appWidgetIds) {
            val views = RemoteViews(context.packageName, R.layout.duitku_widget_large)

            val balance = widgetData.getString(KEY_BALANCE, context.getString(R.string.widget_balance_default))
                ?: context.getString(R.string.widget_balance_default)
            val income = widgetData.getString(KEY_INCOME, context.getString(R.string.widget_income_default))
                ?: context.getString(R.string.widget_income_default)
            val expense = widgetData.getString(KEY_EXPENSE, context.getString(R.string.widget_expense_default))
                ?: context.getString(R.string.widget_expense_default)
            val month = widgetData.getString(KEY_MONTH, context.getString(R.string.widget_month_default))
                ?: context.getString(R.string.widget_month_default)

            views.setTextViewText(R.id.widget_large_balance, balance)
            views.setTextViewText(R.id.widget_large_income, income)
            views.setTextViewText(R.id.widget_large_expense, expense)
            views.setTextViewText(R.id.widget_large_month, month)

            // Top 3 kategori pengeluaran
            bindCategories(context, views, widgetData)

            // Tap widget → buka app
            val launchIntent = Intent(context, MainActivity::class.java).apply {
                flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TOP
            }
            val pendingIntent = PendingIntent.getActivity(
                context,
                appWidgetId,
                launchIntent,
                PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE,
            )
            views.setOnClickPendingIntent(R.id.widget_large_root, pendingIntent)

            // Tombol [+ Transaksi] → buka add transaction
            val addIntent = Intent(context, MainActivity::class.java).apply {
                flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TOP
                putExtra("navigate_to", "add_transaction")
            }
            val addPendingIntent = PendingIntent.getActivity(
                context,
                appWidgetId + 10000,
                addIntent,
                PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE,
            )
            views.setOnClickPendingIntent(R.id.widget_large_btn_add, addPendingIntent)

            // Tombol [Riwayat] → buka history
            val historyIntent = Intent(context, MainActivity::class.java).apply {
                flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TOP
                putExtra("navigate_to", "history")
            }
            val historyPendingIntent = PendingIntent.getActivity(
                context,
                appWidgetId + 20000,
                historyIntent,
                PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE,
            )
            views.setOnClickPendingIntent(R.id.widget_large_btn_history, historyPendingIntent)

            appWidgetManager.updateAppWidget(appWidgetId, views)
        }
    }

    private fun bindCategories(context: Context, views: RemoteViews, prefs: SharedPreferences) {
        val catNames = listOf(
            prefs.getString(KEY_CAT_NAME_1, null),
            prefs.getString(KEY_CAT_NAME_2, null),
            prefs.getString(KEY_CAT_NAME_3, null),
        )
        val catAmounts = listOf(
            prefs.getString(KEY_CAT_AMOUNT_1, null),
            prefs.getString(KEY_CAT_AMOUNT_2, null),
            prefs.getString(KEY_CAT_AMOUNT_3, null),
        )
        val catColors = listOf(
            prefs.getInt(KEY_CAT_COLOR_1, 0xFF64748B.toInt()),
            prefs.getInt(KEY_CAT_COLOR_2, 0xFF94A3B8.toInt()),
            prefs.getInt(KEY_CAT_COLOR_3, 0xFFCBD5E1.toInt()),
        )

        val containerIds = intArrayOf(R.id.widget_category_1, R.id.widget_category_2, R.id.widget_category_3)
        val nameIds = intArrayOf(R.id.widget_category_name_1, R.id.widget_category_name_2, R.id.widget_category_name_3)
        val amountIds = intArrayOf(R.id.widget_category_amount_1, R.id.widget_category_amount_2, R.id.widget_category_amount_3)
        val dotIds = intArrayOf(R.id.widget_category_dot_1, R.id.widget_category_dot_2, R.id.widget_category_dot_3)

        for (i in 0 until 3) {
            val name = catNames[i]
            val amount = catAmounts[i]
            if (!name.isNullOrBlank() && !amount.isNullOrBlank()) {
                views.setViewVisibility(containerIds[i], View.VISIBLE)
                views.setTextViewText(nameIds[i], name)
                views.setTextViewText(amountIds[i], amount)
                views.setInt(dotIds[i], "setBackgroundColor", catColors[i])
            } else {
                views.setViewVisibility(containerIds[i], View.GONE)
            }
        }
    }

    companion object {
        private const val KEY_BALANCE = "widget_balance"
        private const val KEY_INCOME = "widget_income"
        private const val KEY_EXPENSE = "widget_expense"
        private const val KEY_MONTH = "widget_month"
        private const val KEY_CAT_NAME_1 = "widget_cat_name_1"
        private const val KEY_CAT_NAME_2 = "widget_cat_name_2"
        private const val KEY_CAT_NAME_3 = "widget_cat_name_3"
        private const val KEY_CAT_AMOUNT_1 = "widget_cat_amount_1"
        private const val KEY_CAT_AMOUNT_2 = "widget_cat_amount_2"
        private const val KEY_CAT_AMOUNT_3 = "widget_cat_amount_3"
        private const val KEY_CAT_COLOR_1 = "widget_cat_color_1"
        private const val KEY_CAT_COLOR_2 = "widget_cat_color_2"
        private const val KEY_CAT_COLOR_3 = "widget_cat_color_3"
    }
}
