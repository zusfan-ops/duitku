package com.duitku.duitku_app

import android.app.PendingIntent
import android.appwidget.AppWidgetManager
import android.appwidget.AppWidgetProvider
import android.content.ComponentName
import android.content.Context
import android.content.Intent
import android.widget.RemoteViews
import es.antonborri.home_widget.HomeWidgetPlugin

/**
 * Android App Widget untuk DuitKu.
 *
 * Widget ini membaca data saldo terakhir yang disimpan oleh Flutter melalui
 * [HomeWidgetPlugin], lalu menampilkannya di home screen.
 */
class DuitkuWidgetProvider : AppWidgetProvider() {

    override fun onUpdate(
        context: Context,
        appWidgetManager: AppWidgetManager,
        appWidgetIds: IntArray,
    ) {
        val prefs = HomeWidgetPlugin.getData(context)

        for (appWidgetId in appWidgetIds) {
            val views = RemoteViews(context.packageName, R.layout.duitku_widget)

            val balance = prefs.getString(KEY_BALANCE, context.getString(R.string.widget_balance_default)) ?: context.getString(R.string.widget_balance_default)
            val income = prefs.getString(KEY_INCOME, context.getString(R.string.widget_income_default)) ?: context.getString(R.string.widget_income_default)
            val expense = prefs.getString(KEY_EXPENSE, context.getString(R.string.widget_expense_default)) ?: context.getString(R.string.widget_expense_default)
            val month = prefs.getString(KEY_MONTH, context.getString(R.string.widget_month_default)) ?: context.getString(R.string.widget_month_default)

            views.setTextViewText(R.id.widget_balance, balance)
            views.setTextViewText(R.id.widget_income, income)
            views.setTextViewText(R.id.widget_expense, expense)
            views.setTextViewText(R.id.widget_month, month)

            // Saat widget ditekan, buka aplikasi.
            val launchIntent = Intent(context, MainActivity::class.java).apply {
                flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TOP
            }
            val pendingIntent = PendingIntent.getActivity(
                context,
                0,
                launchIntent,
                PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE,
            )
            views.setOnClickPendingIntent(R.id.widget_root, pendingIntent)

            appWidgetManager.updateAppWidget(appWidgetId, views)
        }
    }

    override fun onReceive(context: Context, intent: Intent) {
        super.onReceive(context, intent)
        // Update semua widget saat HomeWidgetPlugin meminta refresh.
        if (intent.action == AppWidgetManager.ACTION_APPWIDGET_UPDATE) {
            val manager = AppWidgetManager.getInstance(context)
            val component = ComponentName(context, DuitkuWidgetProvider::class.java)
            onUpdate(context, manager, manager.getAppWidgetIds(component))
        }
    }

    companion object {
        private const val KEY_BALANCE = "widget_balance"
        private const val KEY_INCOME = "widget_income"
        private const val KEY_EXPENSE = "widget_expense"
        private const val KEY_MONTH = "widget_month"
    }
}
