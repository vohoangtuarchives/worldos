#[cfg(test)]
mod tests {
    use crate::math::core::MathCore;
    use ndarray::{Array1, Array2, arr1, arr2};

    #[test]
    fn test_spectral_radius_identity() {
        let math = MathCore::new(0.25, 0.0, 0.3, 0.01);
        let identity = Array2::<f64>::eye(3);
        
        let rho = math.compute_spectral_radius(&identity);
        assert!((rho - 1.0).abs() < 1e-6, "Spectral radius of Identity should be 1.0, got {}", rho);
    }

    #[test]
    fn test_spectral_radius_zero() {
        let math = MathCore::new(0.25, 0.0, 0.3, 0.01);
        let zero = Array2::<f64>::zeros((3, 3));
        
        let rho = math.compute_spectral_radius(&zero);
        assert!((rho - 0.0).abs() < 1e-6, "Spectral radius of Zero matrix should be 0.0, got {}", rho);
    }

    #[test]
    fn test_contraction_map_stable_regime() {
        // Regime R1: high alpha, high eta -> should be stable
        let math = MathCore::new(0.25, 0.0, 0.3, 0.01);
        
        // A is slightly contractive
        let a_matrix = arr2(&[
            [0.9, 0.1],
            [0.1, 0.9]
        ]);
        let l_matrix = Array2::<f64>::zeros((2, 2));

        let jacobian = math.compute_jacobian(&a_matrix, &l_matrix);
        
        // J = I + 0.25(A - I - 0.3*I)
        let rho = math.compute_spectral_radius(&jacobian);
        
        // Should be definitely less than 1.0
        assert!(rho < 1.0, "Regime R1 should be a contraction map, got rho = {}", rho);
        
        // Verify with delta = 0.05
        let result = math.verify_contraction(&jacobian, 0.05);
        assert!(result.is_ok(), "Should pass verification with delta 0.05");
    }

    #[test]
    fn test_compute_next_state_deterministic() {
        let math = MathCore::new(0.25, 0.0, 0.3, 0.01);
        let jacobian = arr2(&[
            [0.8, 0.1],
            [0.1, 0.8]
        ]);
        let x_t = arr1(&[1.0, 1.0]);
        let u_t = arr1(&[0.5, 0.5]);

        let x_next_1 = math.compute_next_state(&jacobian, &x_t, &u_t);
        let x_next_2 = math.compute_next_state(&jacobian, &x_t, &u_t);

        // AXIOM 1: Determinism
        assert_eq!(x_next_1, x_next_2);
    }
}
