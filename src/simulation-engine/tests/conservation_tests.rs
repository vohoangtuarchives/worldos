use simulation_engine::domain::material::ZoneMaterial;

#[test]
fn test_mass_conservation_extraction() {
    let mut material = ZoneMaterial::new(100.0, 50.0);
    let original_base = material.base_mass;
    
    // Extract mass: Chuyển từ chưa có cấu trúc sang có cấu trúc
    material.extract_and_build(20.0);
    
    // Kiểm tra base_mass không đổi (Sai số cực nhỏ)
    assert!((material.base_mass - original_base).abs() < 1e-12);
    // Structured mass không bao giờ vượt quá base_mass
    assert!(material.structured_mass <= material.base_mass);
}

#[test]
fn test_mass_conservation_decay() {
    let mut material = ZoneMaterial::new(100.0, 50.0);
    material.extract_and_build(50.0);
    let original_base = material.base_mass;
    
    // Phân rã tự nhiên: Structured mass giảm đi
    material.apply_natural_decay();
    
    // Base mass vẫn phải giữ nguyên
    assert!((material.base_mass - original_base).abs() < 1e-12);
    assert!(material.structured_mass >= 0.0);
}

#[test]
fn test_mass_conservation_over_extraction() {
    let mut material = ZoneMaterial::new(100.0, 50.0);
    // Cố gắng khai thác nhiều hơn mức base_mass
    material.extract_and_build(200.0);
    
    // Structured mass chỉ được tối đa là base_mass
    assert!((material.structured_mass - 100.0).abs() < 1e-12);
    assert!((material.base_mass - 100.0).abs() < 1e-12);
}
