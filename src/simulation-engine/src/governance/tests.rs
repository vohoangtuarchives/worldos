#[cfg(test)]
mod tests {
    use crate::governance::guard::GovernanceGuard;
    use ndarray::arr1;

    #[test]
    fn test_input_norm_accept_within_gamma() {
        let guard = GovernanceGuard::new(0.08, 1.5, 1000.0, 1.0);
        let u_t = arr1(&[0.5, 0.5, 0.5]);
        // norm(u_t) = sqrt(0.75) ~ 0.866 < 1.5
        assert!(guard.check_input_norm(&u_t).is_ok());
    }

    #[test]
    fn test_input_norm_reject_exceeds_gamma() {
        let guard = GovernanceGuard::new(0.08, 1.5, 1000.0, 1.0);
        let u_t = arr1(&[1.0, 1.0, 1.0]);
        // norm(u_t) = sqrt(3) ~ 1.732 > 1.5
        assert!(guard.check_input_norm(&u_t).is_err());
    }

    #[test]
    fn test_lyapunov_stable_regime_accepted() {
        let guard = GovernanceGuard::new(0.08, 1.5, 1000.0, 1.0);
        // spectral radius < 1 - 0.08 (0.92)
        assert!(guard.check_lyapunov_stability(0.85).is_ok());
    }

    #[test]
    fn test_lyapunov_unstable_rejected() {
        let guard = GovernanceGuard::new(0.08, 1.5, 1000.0, 1.0);
        // spectral radius >= 1 - 0.08 (0.92)
        assert!(guard.check_lyapunov_stability(0.95).is_err());
    }

    #[test]
    fn test_energy_budget_accept_contracting() {
        let guard = GovernanceGuard::new(0.08, 1.5, 1000.0, 1.0);
        let x_t = arr1(&[10.0, 0.0]);
        let x_next = arr1(&[9.0, 0.0]);
        // r = 9.0 / 10.0 = 0.9 <= 1.0
        assert!(guard.check_energy_budget(&x_t, &x_next).is_ok());
    }

    #[test]
    fn test_energy_budget_reject_expanding() {
        let guard = GovernanceGuard::new(0.08, 1.5, 1000.0, 1.0);
        let x_t = arr1(&[10.0, 0.0]);
        let x_next = arr1(&[11.0, 0.0]);
        // r = 11.0 / 10.0 = 1.1 > 1.0
        assert!(guard.check_energy_budget(&x_t, &x_next).is_err());
    }
}
