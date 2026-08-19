package com.goleaf.plugins.email_picker

import android.accounts.AccountManager
import android.app.Activity
import android.util.Patterns
import androidx.activity.result.contract.ActivityResultContracts
import androidx.fragment.app.Fragment
import androidx.fragment.app.FragmentActivity
import com.nativephp.mobile.bridge.BridgeError
import com.nativephp.mobile.bridge.BridgeFunction
import com.nativephp.mobile.bridge.BridgeResponse
import com.nativephp.mobile.utils.WebViewProvider
import org.json.JSONObject

object EmailPickerFunctions {
    class Choose(private val activity: FragmentActivity) : BridgeFunction {
        override fun execute(parameters: Map<String, Any>): Map<String, Any> {
            if (activity.isFinishing || activity.isDestroyed) {
                throw BridgeError.ExecutionFailed("The account chooser activity is unavailable")
            }

            activity.runOnUiThread {
                EmailPickerResultFragment.install(activity).launchChooser()
            }

            return BridgeResponse.success(mapOf("status" to "launched"))
        }
    }
}

class EmailPickerResultFragment : Fragment() {
    private var chooserActive = false

    private val chooser = registerForActivityResult(
        ActivityResultContracts.StartActivityForResult()
    ) { result ->
        chooserActive = false

        if (result.resultCode != Activity.RESULT_OK) {
            dispatchResult(JSONObject().put("status", "cancelled"))
            return@registerForActivityResult
        }

        val email = result.data
            ?.getStringExtra(AccountManager.KEY_ACCOUNT_NAME)
            ?.trim()

        if (email.isNullOrEmpty() || email.length > 254 || !Patterns.EMAIL_ADDRESS.matcher(email).matches()) {
            dispatchResult(
                JSONObject()
                    .put("status", "error")
                    .put("code", "INVALID_RESULT")
            )
            return@registerForActivityResult
        }

        dispatchResult(
            JSONObject()
                .put("status", "selected")
                .put("email", email)
        )
    }

    fun launchChooser() {
        if (chooserActive) {
            return
        }

        chooserActive = true

        try {
            chooser.launch(
                AccountManager.newChooseAccountIntent(
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null
                )
            )
        } catch (_: RuntimeException) {
            chooserActive = false
            dispatchResult(
                JSONObject()
                    .put("status", "error")
                    .put("code", "LAUNCH_FAILED")
            )
        }
    }

    private fun dispatchResult(payload: JSONObject) {
        val javascript = """
            (() => {
                const detail = ${payload};
                document.dispatchEvent(new CustomEvent("sutelio-email-picker-result", { detail }));
            })();
        """.trimIndent()

        (activity as? WebViewProvider)
            ?.getWebViewOrNull()
            ?.evaluateJavascript(javascript, null)
    }

    companion object {
        private const val TAG = "SutelioEmailPicker"

        fun install(activity: FragmentActivity): EmailPickerResultFragment {
            val existing = activity.supportFragmentManager
                .findFragmentByTag(TAG) as? EmailPickerResultFragment

            if (existing != null) {
                return existing
            }

            return EmailPickerResultFragment().also { fragment ->
                activity.supportFragmentManager
                    .beginTransaction()
                    .add(fragment, TAG)
                    .commitNowAllowingStateLoss()
            }
        }
    }
}
